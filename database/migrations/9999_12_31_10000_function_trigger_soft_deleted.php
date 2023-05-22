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
CREATE OR REPLACE FUNCTION check_soft_deleted_areas() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM majors WHERE area_id = OLD.id and majors.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM enrollment_waves WHERE area_id = OLD.id and enrollment_waves.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM classrooms WHERE area_id = OLD.id and classrooms.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM student_profiles WHERE area_id = OLD.id and student_profiles.deleted_time = 0))) THEN
        RAISE EXCEPTION 'Cannot soft delete areas table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_enrollment_objects() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM major_object_maps WHERE enrollment_object_id = OLD.id) OR
        EXISTS (SELECT 1 FROM training_program_items WHERE enrollment_object_id = OLD.id and enrollment_object_id.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM classrooms WHERE enrollment_object_id = OLD.id and classrooms.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM student_profiles WHERE enrollment_object_id = OLD.id and student_profiles.deleted_time = 0))) THEN
        RAISE EXCEPTION 'Cannot soft delete enrollment_objects table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);

        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_subjects() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM study_plans WHERE subject_id = OLD.id and study_plans.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM learning_modules WHERE subject_id = OLD.id and learning_modules.deleted_time = 0) OR
        EXISTS (SELECT 1 FROM training_program_items WHERE subject_id = OLD.id and training_program_items.deleted_time = 0))) THEN
        RAISE EXCEPTION 'Cannot soft delete subjects table because there are related!';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql
SQL);
        DB::statement(<<<SQL
CREATE OR REPLACE FUNCTION check_soft_deleted_profiles() RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.deleted_time != 0 AND (
        EXISTS (SELECT 1 FROM profile_coffers WHERE profile_id = OLD.id) OR
        EXISTS (SELECT 1 FROM student_profiles WHERE profile_id = OLD.id and student_profiles.deleted_time = 0))) THEN
        RAISE EXCEPTION 'Cannot soft delete profiles table because there are related!';
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
        DB::statement('drop function if exists check_soft_deleted_areas');
        DB::statement('drop function if exists check_soft_deleted_enrollment_objects');
        DB::statement('drop function if exists check_soft_deleted_subjects');
        DB::statement('drop function if exists check_soft_deleted_profiles');
    }
};
