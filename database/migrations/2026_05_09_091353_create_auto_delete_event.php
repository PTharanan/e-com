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
        // 1. Enable Event Scheduler
        DB::unprepared("SET GLOBAL event_scheduler = ON;");

        // 2. Create Wrapper for Orders
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders_wrapper;");
        DB::unprepared("
            CREATE PROCEDURE sp_auto_delete_orders_wrapper(
                IN p_user_id INT, 
                IN p_status VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci, 
                IN p_role VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            )
            BEGIN
                DECLARE v_value INT DEFAULT 0;
                DECLARE v_unit VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'months';
                
                -- Get value
                SELECT CAST(value AS UNSIGNED) INTO v_value 
                FROM site_settings 
                WHERE user_id = p_user_id AND `key` = CONCAT('auto_delete_', p_status, '_value')
                LIMIT 1;

                -- Get unit
                SELECT value INTO v_unit 
                FROM site_settings 
                WHERE user_id = p_user_id AND `key` = CONCAT('auto_delete_', p_status, '_unit')
                LIMIT 1;
                
                IF v_value > 0 THEN
                    CALL sp_auto_delete_orders(p_user_id, p_status, v_value, v_unit, IF(p_role='seller', 1, 0));
                END IF;
            END
        ");

        // 3. Create Wrapper for Notifications
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications_wrapper;");
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

        // 4. Create Master Procedure
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_run_auto_cleanup_all;");
        DB::unprepared("
            CREATE PROCEDURE sp_run_auto_cleanup_all()
            BEGIN
                DECLARE done INT DEFAULT FALSE;
                DECLARE v_user_id INT;
                DECLARE v_role VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
                DECLARE cur1 CURSOR FOR SELECT id, role FROM users;
                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                OPEN cur1;

                read_loop: LOOP
                    FETCH cur1 INTO v_user_id, v_role;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    -- Run for each status
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'delivered', v_role);
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'cancelled', v_role);
                    CALL sp_auto_delete_orders_wrapper(v_user_id, 'refunded', v_role);
                    
                    -- Run for notifications
                    CALL sp_auto_delete_notifications_wrapper(v_user_id);
                END LOOP;

                CLOSE cur1;
            END
        ");

        // 5. Create Event (Runs every hour)
        DB::unprepared("DROP EVENT IF EXISTS e_auto_delete_cleanup;");
        DB::unprepared("
            CREATE EVENT e_auto_delete_cleanup
            ON SCHEDULE EVERY 1 HOUR
            STARTS CURRENT_TIMESTAMP
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
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_notifications_wrapper;");
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_auto_delete_orders_wrapper;");
    }
};
