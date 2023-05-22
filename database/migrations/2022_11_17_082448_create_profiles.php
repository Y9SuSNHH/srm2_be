<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement(<<<SQL
CREATE TABLE public.profiles (
            id bigserial NOT NULL,
            created_at timestamp(0) NULL,
            created_by int8 NULL,
            updated_at timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP,
            updated_by int8 NULL,
            deleted_time int8 NOT NULL DEFAULT '0'::bigint,
            deleted_by int8 NULL,
            firstname varchar(150) NOT NULL,
            lastname varchar(150) NOT NULL,
            gender int2 NOT NULL,
            identification varchar NOT NULL,
            identification_div int2 NOT NULL,
            grant_date date NULL,
            grant_place varchar(255) NOT NULL DEFAULT ''::text,
            main_residence varchar(255) NOT NULL DEFAULT ''::text,
            birthday date NULL,
            borned_year int2 NOT NULL,
            borned_place varchar(255) NULL,
            phone_number varchar NULL,
            nation varchar(100) NULL,
            religion varchar(100) NULL,
            email varchar(255) NULL,
            address varchar(255) NULL,
            curriculum_vitae jsonb NULL,
        
            CONSTRAINT profiles_identification_uk UNIQUE (identification, identification_div, deleted_time),
            CONSTRAINT profiles_pkey PRIMARY KEY (id),
            CONSTRAINT profiles_identification_check CHECK (((identification_div || identification)::text ~ '^[1-8]\d{9}$|^[1-8]\d{12}$|^9\w+$'::text)),
            CONSTRAINT profiles_phone_number_check CHECK ((phone_number)::text ~ '^((\+84|0)\d{9,10})?$'::text)
        );
SQL );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
};
