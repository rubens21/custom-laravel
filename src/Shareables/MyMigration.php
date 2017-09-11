<?php
namespace CST21\Shareables;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Created by IntelliJ IDEA.
 * User: Rubens
 * Date: 2017-01-20
 * Time: 7:41 PM
 */
class MyMigration extends Migration
{
	/**
	 * @param Blueprint $table
	 */
	protected function createTimestamps(Blueprint $table)
    {
        $table->timestamps();
        $table->index('created_at', 'creation_time_index');
        $table->index('updated_at', 'update_time_index');
    }


}