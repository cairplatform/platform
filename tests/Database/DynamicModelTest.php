<?php

use Cair\Platform\Database\DynamicModel;
use Cair\Platform\Database\Command;
use Mockery as m;

class DynamicModelTest extends \PHPUnit_Framework_TestCase {

	function tearDown()
	{
		m::close();
	}

	function test_specifies_resource_and_adds_attributes()
	{
		$model = new DynamicModel('people', [
			'name' => 'basti'
		]);

		$this->assertEquals('people', $model->getResource());
		$this->assertEquals('basti', $model->name);
	}

	function test_can_set_attribute_via_property()
	{
		$model = new DynamicModel('people');

		$model->name = 'basti';

		$this->assertEquals('basti', $model->name);
	}

	function test_can_unset_and_check_set_via_property()
	{
		$model = new DynamicModel('people', [
			'name' => 'basti'
		]);

		$this->assertTrue(isset($model->name));

		unset($model->name);

		$this->assertFalse(isset($model->name));
	}

	function test_can_get_all_records()
	{
		$c = m::mock('Cair\Platform\Database\Command');
		$c->shouldReceive('get')
			  ->once()
			  ->andReturn([['title' => 'Foo']]);
		$model = new DynamicModel('posts');
		$model::setConnection($c);

		$results = $model->get();

		$this->assertTrue(is_array($results));
		$this->assertEquals('Foo', $results[0]->title);
	}

	function test_can_get_record_by_id()
	{
		$c = m::mock('Cair\Platform\Database\Command');
		$c->shouldReceive('find')
			  ->once()
			  ->andReturn(['title' => 'Foo']);
		$model = new DynamicModel('posts');
		$model::setConnection($c);

		$result = $model->find(1);

		$this->assertEquals('Foo', $result->title);
	}

	function test_can_find_and_update()
	{
		$c = m::mock('Cair\Platform\Database\Command');
		$c->shouldReceive('find')
			  ->once()
			  ->andReturn(['title' => 'Foo']);
		$c->shouldReceive('update')
			  ->once()
			  ->andReturn(true);
		$model = new DynamicModel('posts');
		$model::setConnection($c);

		$result = $model->find(1)->update(['title' => 'Bar']);

		$this->assertEquals('Bar', $result->title);
	}

	function test_stores_new_model_on_update_if_current_model_does_not_exist()
	{
		$c = m::mock('Cair\Platform\Database\Command');
		$c->shouldReceive('create')
			  ->once()
			  ->andReturn();
		$model = new DynamicModel('posts');
		$model::setConnection($c);

		$result = $model->update(['title' => 'Bar']);

		$this->assertEquals($result->title, 'Bar');
	}

	function test_can_merge_with_existing_attributes()
	{
		$model = new DynamicModel('posts', ['title' => 'Bar', 'content' => 'Much Content']);

		$model->mergeAttributes([
			'title' => 'Foo',
			'author' => 'John'
		]);

		$this->assertEquals('Foo', $model->title);
		$this->assertEquals('John', $model->author);
		$this->assertEquals('Much Content', $model->content);
	}
}
