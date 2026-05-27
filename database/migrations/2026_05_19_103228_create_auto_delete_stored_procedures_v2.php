<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates all stored procedures and MySQL scheduled event for auto-delete functionality.
     */
    public function up(): void
    {
        // =============================================
        // 1. DROP ALL EXISTING PROCEDURES & EVENTS
        // =============================================
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_order_returns;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_expired_sessions;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_order_returns_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_run_auto_cleanup_all;");
        DB::unprepared("DROP EVENT IF EXISTS e_auto_delete_cleanup;");

        // =============================================
        // 2. PROCEDURE: Delete Orders by Status & Threshold
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_orders(
                IN p_user_id INT,
                IN p_status VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                IN p_value INT,
                IN p_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                IN p_is_seller TINYINT
            )
            BEGIN
                DECLARE v_threshold DATETIME;

                -- Calculate threshold date based on unit
                SET v_threshold = CASE
                    WHEN p_unit = 'minutes' THEN DATE_SUB(NOW(), INTERVAL p_value MINUTE)
                    WHEN p_unit = 'hours' THEN DATE_SUB(NOW(), INTERVAL p_value HOUR)
                    WHEN p_unit = 'days' THEN DATE_SUB(NOW(), INTERVAL p_value DAY)
                    WHEN p_unit = 'months' THEN DATE_SUB(NOW(), INTERVAL p_value MONTH)
                    WHEN p_unit = 'years' THEN DATE_SUB(NOW(), INTERVAL p_value YEAR)
                    ELSE DATE_SUB(NOW(), INTERVAL p_value MONTH)
                END;

                -- Create temporary table to store IDs and file paths for cleanup
                DROP TEMPORARY TABLE IF EXISTS tmp_orders_to_delete;
                CREATE TEMPORARY TABLE tmp_orders_to_delete (
                    order_id BIGINT UNSIGNED,
                    delivery_id BIGINT UNSIGNED,
                    delivery_pickup_image VARCHAR(255),
                    delivery_delivery_image VARCHAR(255)
                );

                -- Insert matching orders into temp table
                INSERT INTO tmp_orders_to_delete (order_id, delivery_id, delivery_pickup_image, delivery_delivery_image)
                SELECT
                    o.id,
                    od.id, od.pickup_image, od.delivery_image
                FROM orders o
                LEFT JOIN order_deliveries od ON o.id = od.order_id
                WHERE o.status = p_status
                AND o.updated_at < v_threshold
                AND (
                    (p_is_seller = 1 AND JSON_CONTAINS(o.items_json, JSON_OBJECT('seller_id', CAST(p_user_id AS UNSIGNED))))
                    OR (p_is_seller = 0 AND o.admin_id = p_user_id)
                );

                -- Return file paths so PHP can delete them from disk
                SELECT * FROM tmp_orders_to_delete;

                -- Delete related order_deliveries first (FK constraint)
                DELETE od FROM order_deliveries od
                INNER JOIN tmp_orders_to_delete t ON od.id = t.delivery_id
                WHERE t.delivery_id IS NOT NULL;

                -- Delete the orders themselves
                DELETE o FROM orders o
                INNER JOIN tmp_orders_to_delete t ON o.id = t.order_id;

                -- Cleanup temp table
                DROP TEMPORARY TABLE IF EXISTS tmp_orders_to_delete;
            END
        ");

        // =============================================
        // 3. PROCEDURE: Delete Read Notifications
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_notifications(
                IN p_user_id INT,
                IN p_days INT
            )
            BEGIN
                DELETE FROM notifications
                WHERE notifiable_id = p_user_id
                AND read_at IS NOT NULL
                AND created_at < DATE_SUB(NOW(), INTERVAL p_days DAY);
            END
        ");

        // =============================================
        // 4. PROCEDURE: Delete Order Returns (completed/rejected)
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_order_returns(
                IN p_user_id INT,
                IN p_value INT,
                IN p_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                IN p_is_seller TINYINT
            )
            BEGIN
                DECLARE v_threshold DATETIME;

                SET v_threshold = CASE
                    WHEN p_unit = 'minutes' THEN DATE_SUB(NOW(), INTERVAL p_value MINUTE)
                    WHEN p_unit = 'hours' THEN DATE_SUB(NOW(), INTERVAL p_value HOUR)
                    WHEN p_unit = 'days' THEN DATE_SUB(NOW(), INTERVAL p_value DAY)
                    WHEN p_unit = 'months' THEN DATE_SUB(NOW(), INTERVAL p_value MONTH)
                    WHEN p_unit = 'years' THEN DATE_SUB(NOW(), INTERVAL p_value YEAR)
                    ELSE DATE_SUB(NOW(), INTERVAL p_value MONTH)
                END;

                -- Create temporary table to store return IDs to delete
                DROP TEMPORARY TABLE IF EXISTS tmp_returns_to_delete;
                CREATE TEMPORARY TABLE tmp_returns_to_delete (
                    return_id BIGINT UNSIGNED,
                    pickup_img VARCHAR(255),
                    store_img VARCHAR(255)
                );

                -- Insert matching return requests by joining with orders table to match admin/seller ownership
                INSERT INTO tmp_returns_to_delete (return_id, pickup_img, store_img)
                SELECT r.id, r.pickup_image, r.store_image
                FROM order_returns r
                INNER JOIN orders o ON r.order_id = o.id
                WHERE r.status IN ('completed', 'rejected')
                AND r.updated_at < v_threshold
                AND (
                    (p_is_seller = 1 AND JSON_CONTAINS(o.items_json, JSON_OBJECT('seller_id', CAST(p_user_id AS UNSIGNED))))
                    OR (p_is_seller = 0 AND o.admin_id = p_user_id)
                );

                -- Return file paths so php can delete files from disk
                SELECT return_id AS id, pickup_img AS pickup_image, store_img AS store_image FROM tmp_returns_to_delete;

                -- Delete records
                DELETE FROM order_returns
                WHERE id IN (SELECT return_id FROM tmp_returns_to_delete);

                DROP TEMPORARY TABLE IF EXISTS tmp_returns_to_delete;
            END
        ");

        // =============================================
        // 5. PROCEDURE: Cleanup Expired Sessions
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_expired_sessions(
                IN p_max_lifetime_seconds INT
            )
            BEGIN
                DELETE FROM sessions
                WHERE last_activity < (UNIX_TIMESTAMP() - p_max_lifetime_seconds);
            END
        ");

        // =============================================
        // 6. WRAPPER: Orders Wrapper (reads settings from site_settings)
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_orders_wrapper(
                IN p_user_id INT,
                IN p_status VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                IN p_role VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            )
            BEGIN
                DECLARE v_value INT DEFAULT 0;
                DECLARE v_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'months';

                -- Read retention value from site_settings
                SELECT CAST(value AS UNSIGNED) INTO v_value
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = CONCAT('auto_delete_', p_status, '_value')
                LIMIT 1;

                -- Read retention unit from site_settings
                SELECT value INTO v_unit
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = CONCAT('auto_delete_', p_status, '_unit')
                LIMIT 1;

                -- Only run if value > 0
                IF v_value > 0 THEN
                    CALL sp_auto_delete_orders(p_user_id, p_status, v_value, v_unit, IF(p_role='seller', 1, 0));
                END IF;
            END
        ");

        // =============================================
        // 7. WRAPPER: Notifications Wrapper
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_notifications_wrapper(IN p_user_id INT)
            BEGIN
                DECLARE v_days INT DEFAULT 0;

                SELECT CAST(value AS UNSIGNED) INTO v_days
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = 'auto_delete_notifications_days'
                LIMIT 1;

                IF v_days > 0 THEN
                    CALL sp_auto_delete_notifications(p_user_id, v_days);
                END IF;
            END
        ");

        // =============================================
        // 8. WRAPPER: Order Returns Wrapper
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_order_returns_wrapper(
                IN p_user_id INT,
                IN p_role VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            )
            BEGIN
                DECLARE v_value INT DEFAULT 0;
                DECLARE v_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'months';

                -- Read retention value from site_settings
                SELECT CAST(value AS UNSIGNED) INTO v_value
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = 'auto_delete_returns_value'
                LIMIT 1;

                -- Read retention unit from site_settings
                SELECT value INTO v_unit
                FROM site_settings
                WHERE user_id = p_user_id AND `key` = 'auto_delete_returns_unit'
                LIMIT 1;

                -- Only run if value > 0
                IF v_value > 0 THEN
                    CALL sp_auto_delete_order_returns(p_user_id, v_value, v_unit, IF(p_role='seller', 1, 0));
                END IF;
            END
        ");

        // =============================================
        // 9. MASTER: Run All Cleanup for All Users
        // =============================================
        DB::unprepared("
            CREATE PROCEDURE sp_run_auto_cleanup_all()
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE v_user_id INT;
                DECLARE v_role VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                DECLARE cur1 CURSOR FOR
                    SELECT id, role FROM users WHERE role IN ('admin', 'seller');
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                OPEN cur1;

                read_loop: LOOP
                    FETCH cur1 INTO v_user_id, v_role;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    -- Auto-delete orders by status
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'delivered', v_role);
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'cancelled', v_role);
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'refunded', v_role);

                    -- Auto-delete read notifications
                    CALL sp_auto_delete_notifications_wrapper(v_user_id);

                    -- Auto-delete completed or rejected order returns
                    CALL sp_auto_delete_order_returns_wrapper(v_user_id, v_role);
                END LOOP;

                CLOSE cur1;

                -- Cleanup expired sessions (older than 24 hours = 86400 seconds)
                CALL sp_auto_delete_expired_sessions(86400);
            END
        ");

        // =============================================
        // 10. MYSQL EVENT: Run every 1 hour automatically
        // =============================================
        DB::unprepared("SET GLOBAL event_scheduler = ON;");

        DB::unprepared("
            CREATE EVENT e_auto_delete_cleanup
            ON SCHEDULE EVERY 1 HOUR
            STARTS CURRENT_TIMESTAMP
            ON COMPLETION PRESERVE
            COMMENT 'Hourly auto-delete cleanup for orders, notifications, sessions, returns'
            DO CALL sp_run_auto_cleanup_all();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP EVENT IF EXISTS e_auto_delete_cleanup;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_run_auto_cleanup_all;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_order_returns_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_expired_sessions;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_order_returns;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders;");
    }
};
