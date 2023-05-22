<?php

use Illuminate\Database\Migrations\Migration;;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_request_structures() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM requests WHERE request_structure_id = OLD.id and requests.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM request_values WHERE request_structure_id = OLD.id and request_values.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM request_approval_steps WHERE request_structure_id = OLD.id and request_approval_steps.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM request_structure_forms WHERE request_structure_id = OLD.id and request_structure_forms.deleted_time = 0))) THEN
        RAISE EXCEPTION '#EC1.request_structures: Cannot soft delete request_structures table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);
        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_requests() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM request_flows WHERE request_id = OLD.id and request_flows.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM request_values WHERE request_id = OLD.id and request_values.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM request_form_values WHERE request_id = OLD.id and request_form_values.deleted_time = 0))) THEN
        RAISE EXCEPTION '#EC1.requests: Cannot soft delete requests table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);
        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_approval_group_users() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM map_approval_group_users WHERE approval_group_user_id = OLD.id) OR
        EXISTS (SELECT 1 FROM request_approval_steps WHERE approval_group_user_id = OLD.id and request_approval_steps.deleted_time = 0))) THEN
        RAISE EXCEPTION '#EC1.approval_group_users: Cannot soft delete approval_group_users table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);
        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_request_approval_steps() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM request_flows WHERE request_approval_step_id = OLD.id and request_flows.deleted_time = 0))) THEN
        RAISE EXCEPTION '#EC1.request_approval_steps: Cannot soft delete request_approval_steps table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);
        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_request_structure_forms() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM request_form_values WHERE request_structure_form_id = OLD.id and request_form_values.deleted_time = 0))) THEN
        RAISE EXCEPTION '#EC1.request_structure_forms: Cannot soft delete request_structure_forms table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('drop function if exists check_soft_deleted_request_structures');
        DB::statement('drop function if exists check_soft_deleted_requests');
        DB::statement('drop function if exists check_soft_deleted_approval_group_users');
        DB::statement('drop function if exists check_soft_deleted_request_approval_steps');
        DB::statement('drop function if exists check_soft_deleted_request_structure_forms');
    }
};
