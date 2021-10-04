<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddmenuCredentialsSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permission = Permission::updateOrCreate([
			'name' => 'credentials_settings',
			'parent_id' => 2319,
			'order' => 05,
			'is_menu' => 1,
			'url' => '/admin/settings/credentials',
			'icon' => 'mdi mdi-user-lock'
		]);

		if($permission){
			$profile1 = Profile::find(1);
			if($profile1)
				$profile1->permissions()->attach($permission->id);

			$profile4 = Profile::find(4);
			if($profile4)
				$profile4->permissions()->attach($permission->id);
			
            $profile7 = Profile::find(7);
            if($profile7)
                $profile7->permissions()->attach($permission->id);
		}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
