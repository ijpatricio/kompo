<?php

namespace Kompo\Tests\Feature\Select;

use Kompo\Tests\EnvironmentBoot;
use Kompo\Tests\Models\File;

abstract class SelectEnvironmentBootManyTest extends EnvironmentBoot
{
    abstract protected function assert_database_has_expected_row($file, $type = null);

    protected function assert_crud_many_selects($formClass, $relation, $snaked, $index)
    {	
        $type = substr($relation, -7) == 'Ordered' ? 'ordered' : (substr($relation, -8) == 'Filtered' ? 'filtered' : '');
        
    	//Insert
        $file1 = factory(File::class)->create(['name' => 'g']); 
        $file2 = factory(File::class)->create(['name' => 'a']); 
        
        $this->submit($form = new $formClass(), [
    		$relation => [$file1->id, $file2->id]
    	])
        ->assertStatus(201)
        ->assertJson([
        	$snaked => $type == 'ordered' ? [$file2->toArray(), $file1->toArray()] :	[$file1->toArray(), $file2->toArray()]
        ]);

        $this->assert_database_has_expected_row($file1, $type);
        $this->assert_database_has_expected_row($file2, $type);

        //Reload
        $form = new $formClass(1);
        $this->assertCount(2, $form->komponents[$index]->value);
        if($type == 'ordered'){
        	$this->assertEquals($file2->id, $form->komponents[$index]->value[0]);
        	$this->assertEquals($file1->id, $form->komponents[$index]->value[1]);
        }else{
        	$this->assertEquals($file1->id, $form->komponents[$index]->value[0]);
        	$this->assertEquals($file2->id, $form->komponents[$index]->value[1]);
        }


		//Update
        $file3 = factory(File::class)->create(['name' => 'z']); 
        $file4 = factory(File::class)->create(['name' => 'f']);

		$this->submit($form = new $formClass(1), [
    		$relation => [$file1->id, $file3->id, $file4->id]
    	])
        ->assertStatus(200)
        ->assertJson([$snaked => 
             $type == 'ordered' ?  [$file4->toArray(), $file1->toArray(), $file3->toArray()] : 
            ($type == 'filtered' ? [$file1->toArray(), $file4->toArray()] : 
                                   [$file1->toArray(), $file3->toArray(), $file4->toArray()])
                            
        ]);

        $this->assertEquals(3, \DB::table('file_obj')->count());
        $this->assert_database_has_expected_row($file1, $type);
        $this->assert_database_has_expected_row($file3, $type);
        $this->assert_database_has_expected_row($file4, $type);

        //Reload
        $form = new $formClass(1);
        if($type == 'ordered'){
            $this->assertCount(3, $form->komponents[$index]->value);
            $this->assertEquals($file4->id, $form->komponents[$index]->value[0]);
            $this->assertEquals($file1->id, $form->komponents[$index]->value[1]);
            $this->assertEquals($file3->id, $form->komponents[$index]->value[2]);

	    }elseif($type == 'filtered'){
            $this->assertCount(2, $form->komponents[$index]->value);
            $this->assertEquals($file1->id, $form->komponents[$index]->value[0]);
            $this->assertEquals($file4->id, $form->komponents[$index]->value[1]);

        }else{
            $this->assertCount(3, $form->komponents[$index]->value);
            $this->assertEquals($file1->id, $form->komponents[$index]->value[0]);
            $this->assertEquals($file3->id, $form->komponents[$index]->value[1]);
            $this->assertEquals($file4->id, $form->komponents[$index]->value[2]);
	    }

		//Remove
		$this->submit(
        	$form = new $formClass(1), [
        		$relation => null
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => null
        ]);

        $this->assertEquals(0, \DB::table('file_obj')->count());

        //Reload
        $form = new $formClass(1);
        $this->assertNull($form->komponents[$index]->value);
    }

}