<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNestedsetForMenuItemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->integer('parent_id')->nullable()->after('resource');
            $table->integer('lft')->nullable()->after('parent_id');
            $table->integer('rgt')->nullable()->after('lft');
            $table->integer('depth')->nullable()->after('rgt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('parent_id', 'lft', 'rgt', 'depth');
        });
    }
}
