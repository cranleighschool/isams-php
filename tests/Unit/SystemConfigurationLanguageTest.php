<?php

namespace spkm\isams\Tests\Unit;

use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\LanguageController;
use spkm\isams\School;
use spkm\isams\Wrappers\Language;
use spkm\isams\Tests\TestCase;

class SystemConfigurationLanguageTest extends TestCase
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
    public function it_returns_all_languages_as_a_collection_of_language_classes_stored_in_cache()
    {
        $languages = (new LanguageController($this->school))->index();

        foreach ($languages as $language):
            $this->assertTrue(is_a($language, Language::class));

        $properties = ['id', 'description', 'listType', 'name'];
        foreach ($properties as $property):
                $this->assertTrue(array_key_exists($property, $language));
        endforeach;
        endforeach;

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'languages.index'));
    }

    /** @test */
    public function it_creates_a_new_language()
    {
        $response = (new LanguageController($this->school))->store([
            'name' => 'MyNewLanguage',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_a_language()
    {
        //Create it
        $newLanguage = 'MyNewLanguage';
        (new LanguageController($this->school))->store([
            'name' => $newLanguage,
        ]);

        //Find it
        $languages = (new LanguageController($this->school))->index();
        $toDelete = ($this->findLanguageByName($newLanguage, $languages->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new LanguageController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /** @test */
    public function it_updates_a_language()
    {
        //Create it
        $newLanguage = 'MyNewLanguage';
        (new LanguageController($this->school))->store([
            'name' => $newLanguage,
        ]);

        //Find it
        $languages = (new LanguageController($this->school))->index();
        $toUpdate = ($this->findLanguageByName($newLanguage, $languages->toArray()));

        //Update it
        $renameLanguage = 'MySpecialLanguage';
        $response = (new LanguageController($this->school))->update($toUpdate[0], ['name' => $renameLanguage]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $languages = (new LanguageController($this->school))->index();
        $toDelete = ($this->findLanguageByName($renameLanguage, $languages->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new LanguageController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /**
     * Find the elements by name.
     *
     * @param string $name
     * @param array $languages
     * @return array
     */
    private function findLanguageByName(string $name, array $languages)
    {
        $matches = [];
        foreach ($languages as $element):
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        endforeach;

        return $matches;
    }
}
