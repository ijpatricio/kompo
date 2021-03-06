<?php

namespace Kompo\Tests\Feature\Place;

class PlacesStoredAsHasOneManyTest extends PlaceEnvironmentBoot
{
    /** @test */
    public function place_upload_works_with_has_one_plain_crud()
    {
    	$this->assert_has_one_place('hasOnePlain2', 'has_one_plain2', 0);
    }

    /** @test */
    public function place_upload_works_with_has_one_ordered_crud()
    {
    	$this->assert_has_one_place('hasOneOrdered2', 'has_one_ordered2', 1);
    }
    
    /** @test */
    public function place_upload_works_with_has_one_filtered_crud()
    {
    	$this->assert_has_one_place('hasOneFiltered2', 'has_one_filtered2', 2);
    }

    /** @test */
    public function place_upload_works_with_has_many_plain_crud()
    {
    	$this->assert_has_many_places('hasManyPlain2', 'has_many_plain2', 3);
    }
    
    /** @test */
    public function place_upload_works_with_has_many_ordered_crud()
    {
    	$this->assert_has_many_places('hasManyOrdered2', 'has_many_ordered2', 4);
    }
    
    /** @test */
    public function place_upload_works_with_has_many_filtered_crud()
    {
    	$this->assert_has_many_places('hasManyFiltered2', 'has_many_filtered2', 5);
    }


    /** ------------------ PRIVATE --------------------------- */ 


    private function assert_has_one_place($relation, $snaked, $index)
    {	
	    //Insert
        $this->submit(
        	$form = new _PlacesStoredAsHasOneHasManyForm(), [
        		$relation => [$place1 = $this->createPlace('123 St B') ]
        	]
        )->assertStatus(201)
        ->assertJson([
        	$snaked => $this->place_to_array($place1)
        ]);

        $this->assertDatabaseHas('places', $this->place_to_array($place1));

        //Reload
        $form = new _PlacesStoredAsHasOneHasManyForm(1);
        $this->assertEquals(1, $form->komponents[$index]->value->id);
        $this->assertSubset($this->place_to_array($place1), $form->komponents[$index]->value);

		//Update
		$this->submit(
        	$form = new _PlacesStoredAsHasOneHasManyForm(1), [
        		$relation => [ $place2 = $this->createPlace('456 St A') ]
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => $this->place_to_array($place2)
        ]);

        $this->assertDatabaseHas('places', $this->place_to_array($place2));

        //Reload
        $form = new _PlacesStoredAsHasOneHasManyForm(1);
        $this->assertEquals(2, $form->komponents[$index]->value->id);
        $this->assertSubset($this->place_to_array($place2), $form->komponents[$index]->value);

		//Remove
		$this->submit(
        	$form = new _PlacesStoredAsHasOneHasManyForm(1), [
        		$relation => null
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => null
        ]);

        $this->assertDatabaseMissing('places', $this->place_to_array($place2));
        $this->assertEquals(0, \DB::table('places')->count());

        //Reload
        $form = new _PlacesStoredAsHasOneHasManyForm(1);
        $this->assertNull($form->komponents[$index]->value);
    }

    private function assert_has_many_places($relation, $snaked, $index)
    {	
    	//Insert
        $this->submit(
        	$form = new _PlacesStoredAsHasOneHasManyForm(), [
        		$relation => [$place1 = $this->createPlace('83 St B'), $place2 = $this->createPlace('123 St B')]
        	]
        )->assertStatus(201)
        ->assertJson([
        	$snaked => $relation == 'hasManyOrdered2' ? 
        		[$this->place_to_array($place2), $this->place_to_array($place1)] :
        		[$this->place_to_array($place1), $this->place_to_array($place2)]
        ]);

        $this->assertDatabaseHas('places', $this->place_to_array($place1));
        $this->assertDatabaseHas('places', $this->place_to_array($place2));

        //Reload
        $form = new _PlacesStoredAsHasOneHasManyForm(1);
        $this->assertCount(2, $form->komponents[$index]->value);
        if($relation == 'hasManyOrdered2'){
        	$this->assertSubset($this->place_to_array($place2), $form->komponents[$index]->value[0]);
        	$this->assertSubset($this->place_to_array($place1), $form->komponents[$index]->value[1]);
        }else{
        	$this->assertSubset($this->place_to_array($place1), $form->komponents[$index]->value[0]);
        	$this->assertSubset($this->place_to_array($place2), $form->komponents[$index]->value[1]);
        }
        if($relation == 'hasManyFiltered2')
            $this->assertEquals(1, $form->komponents[$index]->value[0]->order);


		//Update
		$this->submit(
        	$form = new _PlacesStoredAsHasOneHasManyForm(1), [
        		$relation => [$place1, $place3 = $this->createPlace('93 St B'), $place4 = $this->createPlace('23 St B')]
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => $relation == 'hasManyOrdered2' ? 
        		[$this->place_to_array($place4), $this->place_to_array($place1), $this->place_to_array($place3)] :
        		[$this->place_to_array($place1), $this->place_to_array($place3), $this->place_to_array($place4)]
        ]);

        $this->assertDatabaseHas('places', $this->place_to_array($place1));
        $this->assertDatabaseMissing('places', $this->place_to_array($place2));
        $this->assertDatabaseHas('places', $this->place_to_array($place3));
        $this->assertDatabaseHas('places', $this->place_to_array($place4));

        //Reload
        $form = new _PlacesStoredAsHasOneHasManyForm(1);
        $this->assertCount(3, $form->komponents[$index]->value);
        if($relation == 'hasManyOrdered2'){
	        $this->assertSubset($this->place_to_array($place4), $form->komponents[$index]->value[0]);
	        $this->assertSubset($this->place_to_array($place1), $form->komponents[$index]->value[1]);
	        $this->assertSubset($this->place_to_array($place3), $form->komponents[$index]->value[2]);
	    }else{
	        $this->assertSubset($this->place_to_array($place1), $form->komponents[$index]->value[0]);
	        $this->assertSubset($this->place_to_array($place3), $form->komponents[$index]->value[1]);
	        $this->assertSubset($this->place_to_array($place4), $form->komponents[$index]->value[2]);
	    }
        if($relation == 'hasManyFiltered2')
            $this->assertEquals(1, $form->komponents[$index]->value[0]->order);

		//Remove
		$this->submit(
        	$form = new _PlacesStoredAsHasOneHasManyForm(1), [
        		$relation => null
        	]
        )->assertStatus(200)
        ->assertJson([
        	$snaked => null
        ]);

        $this->assertDatabaseMissing('places', $this->place_to_array($place1));
        $this->assertDatabaseMissing('places', $this->place_to_array($place3));
        $this->assertDatabaseMissing('places', $this->place_to_array($place4));
        $this->assertEquals(0, \DB::table('places')->count());

        //Reload
        $form = new _PlacesStoredAsHasOneHasManyForm(1);
        $this->assertNull($form->komponents[$index]->value);
    }
}