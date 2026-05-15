<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop existing procedures
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications;");

        // Procedure for deleting orders
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
                
                SET v_threshold = CASE 
                    WHEN p_unit = 'minutes' THEN DATE_SUB(NOW(), INTERVAL p_value MINUTE)
                    WHEN p_unit = 'hours' THEN DATE_SUB(NOW(), INTERVAL p_value HOUR)
                    WHEN p_unit = 'days' THEN DATE_SUB(NOW(), INTERVAL p_value DAY)
                    WHEN p_unit = 'months' THEN DATE_SUB(NOW(), INTERVAL p_value MONTH)
                    WHEN p_unit = 'years' THEN DATE_SUB(NOW(), INTERVAL p_value YEAR)
                    ELSE DATE_SUB(NOW(), INTERVAL p_value MONTH)
                END;

                -- Create temporary table to store IDs and file paths
                CREATE TEMPORARY TABLE IF NOT EXISTS tmp_orders_to_delete (
                    order_id INT,
                    delivery_id INT,
                    delivery_pickup_image VARCHAR(255),
                    delivery_delivery_image VARCHAR(255)
                );
                
                DELETE FROM tmp_orders_to_delete;

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

                -- Return the results for file deletion in PHP
                SELECT * FROM tmp_orders_to_delete;

                -- Perform deletion
                DELETE od FROM order_deliveries od
                INNER JOIN tmp_orders_to_delete t ON od.id = t.delivery_id;

                DELETE o FROM orders o
                INNER JOIN tmp_orders_to_delete t ON o.id = t.order_id;
            END
        ");

        // Procedure for deleting notifications
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications;");
    }
};
