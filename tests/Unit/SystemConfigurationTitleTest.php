<?php

namespace spkm\isams\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\TitleController;
use spkm\isams\School;
use spkm\isams\Tests\TestCase;
use spkm\isams\Wrappers\Title;

class SystemConfigurationTitleTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    public function __construct()
    {
        parent::__construct();

        $this->school = new School();
    }

    /** @test */
    public function it_returns_all_titles_as_a_collection_of_title_classes_stored_in_cache()
    {
        $titles = (new TitleController($this->school))->index();

        foreach ($titles as $title):
            $this->assertTrue(is_a($title, Title::class));

        $properties = ['id', 'description', 'listType', 'name'];
        foreach ($properties as $property):
                $this->assertTrue(array_key_exists($property, $title));
        endforeach;
        endforeach;

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'titles.index'));
    }

    /** @test */
    public function it_creates_a_new_title()
    {
        $response = (new TitleController($this->school))->store([
            'name' => 'MyNewTitle',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_a_title()
    {
        //Create it
        $newTitle = 'MyNewTitle';
        (new TitleController($this->school))->store([
            'name' => $newTitle,
        ]);

        //Find it
        $titles = (new TitleController($this->school))->index();
        $toDelete = ($this->findTitleByName($newTitle, $titles->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new TitleController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /** @test */
    public function it_updates_a_title()
    {
        //Create it
        $newTitle = 'MyNewTitle';
        (new TitleController($this->school))->store([
            'name' => $newTitle,
        ]);

        //Find it
        $titles = (new TitleController($this->school))->index();
        $toUpdate = ($this->findTitleByName($newTitle, $titles->toArray()));

        //Update it
        $renameTitle = 'MySpecialTitle';
        $response = (new TitleController($this->school))->update($toUpdate[0], ['name' => $renameTitle]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $titles = (new TitleController($this->school))->index();
        $toDelete = ($this->findTitleByName($renameTitle, $titles->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new TitleController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /**
     * Find the elements by name.
     *
     * @param string $name
     * @param array $titles
     * @return array
     */
    private function findTitleByName(string $name, array $titles)
    {
        $matches = [];
        foreach ($titles as $element):
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        endforeach;

        return $matches;
    }
}
