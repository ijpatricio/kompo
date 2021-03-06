<?php

namespace Kompo\Tests\Feature\File;

class FilesStoredAsHasOneManyTest extends FileEnvironmentBoot
{
	public $modelPath = 'sqlite/files';

    /** @test */
    public function file_upload_works_with_has_one_plain_crud()
    {
    	$this->assert_has_one_file('hasOnePlain', 'has_one_plain', 0);
    }

    /** @test */
    public function file_upload_works_with_has_one_ordered_crud()
    {
    	$this->assert_has_one_file('hasOneOrdered', 'has_one_ordered', 1);
    }
    
    /** @test */
    public function file_upload_works_with_has_one_filtered_crud()
    {
    	$this->assert_has_one_file('hasOneFiltered', 'has_one_filtered', 2);
    }

    /** @test */
    public function file_upload_works_with_has_many_plain_crud()
    {
    	$this->assert_has_many_files('hasManyPlain', 'has_many_plain', 3);
    }
    
    /** @test */
    public function file_upload_works_with_has_many_ordered_crud()
    {
    	$this->assert_has_many_files('hasManyOrdered', 'has_many_ordered', 4);
    }
    
    /** @test */
    public function file_upload_works_with_has_many_filtered_crud()
    {
    	$this->assert_has_many_files('hasManyFiltered', 'has_many_filtered', 5);
    }


    /** ------------------ PRIVATE --------------------------- */ 


    private function assert_has_one_file($relation, $snaked, $index)
    {	
	    //Insert
        $this->submit(
        	$form = new _FilesStoredAsHasOneHasManyForm(), [
        		$relation => ( $file1 = $this->createFile('b.pdf') )
        	]
        )->assertStatus(201)
        ->assertJson([
        	$snaked => $this->file_to_array($file1, 'path')
        ]);

	    $this->assert_in_storage($file1, 'path');
        $this->assertDatabaseHas('files', $this->file_to_array($file1, 'path'));

        //Reload
        $form = new _FilesStoredAsHasOneHasManyForm(1);
        $this->assertEquals(1, $form->komponents[$index]->value->id);
        $this->assertSubset($this->file_to_array($file1, 'path'), $form->komponents[$index]->value);

		//Update
		$this->submit(
        	$form = new _FilesStoredAsHasOneHasManyForm(1), [
        		$relation => ( $file2 = $this->createFile('a.pdf') )
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => $this->file_to_array($file2, 'path')
        ]);

	    $this->assert_in_storage($file2, 'path');
        $this->assertDatabaseHas('files', $this->file_to_array($file2, 'path'));

        //Reload
        $form = new _FilesStoredAsHasOneHasManyForm(1);
        $this->assertEquals(2, $form->komponents[$index]->value->id);
        $this->assertSubset($this->file_to_array($file2, 'path'), $form->komponents[$index]->value);

		//Remove
		$this->submit(
        	$form = new _FilesStoredAsHasOneHasManyForm(1), [
        		$relation => null
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => null
        ]);

	    $this->assert_not_in_storage($file2, 'path');
        $this->assertDatabaseMissing('files', $this->file_to_array($file2, 'path'));
        $this->assertEquals(0, \DB::table('files')->count());

        //Reload
        $form = new _FilesStoredAsHasOneHasManyForm(1);
        $this->assertNull($form->komponents[$index]->value);
    }

    private function assert_has_many_files($relation, $snaked, $index)
    {	
    	//Insert
        $this->submit(
        	$form = new _FilesStoredAsHasOneHasManyForm(), [
        		$relation => [$file1 = $this->createFile('m.pdf'), $file2 = $this->createFile('a.pdf')]
        	]
        )->assertStatus(201)
        ->assertJson([
        	$snaked => $relation == 'hasManyOrdered' ? 
        		[$this->file_to_array($file2, 'path'), $this->file_to_array($file1, 'path')] :
        		[$this->file_to_array($file1, 'path'), $this->file_to_array($file2, 'path')]
        ]);

	    $this->assert_in_storage($file1, 'path');
	    $this->assert_in_storage($file2, 'path');
        $this->assertDatabaseHas('files', $this->file_to_array($file1, 'path'));
        $this->assertDatabaseHas('files', $this->file_to_array($file2, 'path'));

        //Reload
        $form = new _FilesStoredAsHasOneHasManyForm(1);
        $this->assertCount(2, $form->komponents[$index]->value);
        if($relation == 'hasManyOrdered'){
        	$this->assertSubset($this->file_to_array($file2, 'path'), $form->komponents[$index]->value[0]);
        	$this->assertSubset($this->file_to_array($file1, 'path'), $form->komponents[$index]->value[1]);
        }else{
        	$this->assertSubset($this->file_to_array($file1, 'path'), $form->komponents[$index]->value[0]);
        	$this->assertSubset($this->file_to_array($file2, 'path'), $form->komponents[$index]->value[1]);
        }
        if($relation == 'hasManyFiltered')
            $this->assertEquals(1, $form->komponents[$index]->value[0]->order);


		//Update
		$this->submit(
        	$form = new _FilesStoredAsHasOneHasManyForm(1), [
        		$relation => [$file1, $file3 = $this->createFile('z.pdf'), $file4 = $this->createFile('b.pdf')]
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => $relation == 'hasManyOrdered' ? 
        		[$this->file_to_array($file4, 'path'), $this->file_to_array($file1, 'path'), $this->file_to_array($file3, 'path')] :
        		[$this->file_to_array($file1, 'path'), $this->file_to_array($file3, 'path'), $this->file_to_array($file4, 'path')]
        ]);

	    $this->assert_in_storage($file1, 'path');
	    $this->assert_not_in_storage($file2, 'path');
	    $this->assert_in_storage($file3, 'path');
	    $this->assert_in_storage($file4, 'path');
        $this->assertDatabaseHas('files', $this->file_to_array($file1, 'path'));
        $this->assertDatabaseMissing('files', $this->file_to_array($file2, 'path'));
        $this->assertDatabaseHas('files', $this->file_to_array($file3, 'path'));
        $this->assertDatabaseHas('files', $this->file_to_array($file4, 'path'));

        //Reload
        $form = new _FilesStoredAsHasOneHasManyForm(1);
        $this->assertCount(3, $form->komponents[$index]->value);
        if($relation == 'hasManyOrdered'){
	        $this->assertSubset($this->file_to_array($file4, 'path'), $form->komponents[$index]->value[0]);
	        $this->assertSubset($this->file_to_array($file1, 'path'), $form->komponents[$index]->value[1]);
	        $this->assertSubset($this->file_to_array($file3, 'path'), $form->komponents[$index]->value[2]);
	    }else{
	        $this->assertSubset($this->file_to_array($file1, 'path'), $form->komponents[$index]->value[0]);
	        $this->assertSubset($this->file_to_array($file3, 'path'), $form->komponents[$index]->value[1]);
	        $this->assertSubset($this->file_to_array($file4, 'path'), $form->komponents[$index]->value[2]);
	    }
        if($relation == 'hasManyFiltered')
            $this->assertEquals(1, $form->komponents[$index]->value[0]->order);

		//Remove
		$this->submit(
        	$form = new _FilesStoredAsHasOneHasManyForm(1), [
        		$relation => null
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => null
        ]);

	    $this->assert_not_in_storage($file1, 'path');
	    $this->assert_not_in_storage($file3, 'path');
	    $this->assert_not_in_storage($file4, 'path');
        $this->assertDatabaseMissing('files', $this->file_to_array($file1, 'path'));
        $this->assertDatabaseMissing('files', $this->file_to_array($file3, 'path'));
        $this->assertDatabaseMissing('files', $this->file_to_array($file4, 'path'));
        $this->assertEquals(0, \DB::table('files')->count());

        //Reload
        $form = new _FilesStoredAsHasOneHasManyForm(1);
        $this->assertNull($form->komponents[$index]->value);
    }
}