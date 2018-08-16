<?php

namespace spkm\isams\Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\Country;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\CountryController;

class SystemConfigurationCountriesTest extends TestCase
{
    /**
     * @var School
     */
    protected $school;

    public function __construct()
    {
        parent::__construct();

        $this->school = new School;
    }

    /** @test */
    public function it_returns_all_countries_as_a_collection_of_country_classes_stored_in_cache()
    {
        $countries = (new CountryController($this->school))->index();

        foreach ($countries as $country):
            $this->assertTrue(is_a($country, Country::class));

            $properties = ['id', 'description', 'listType', 'name'];
            foreach ($properties as $property):
                $this->assertTrue(array_key_exists($property, $country));
            endforeach;
        endforeach;

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'countries.index'));
    }

    /** @test */
    public function it_creates_a_new_country()
    {
        $response = (new CountryController($this->school))->store([
            'name' => 'MyNewCountry',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_a_country()
    {
        //Create it
        $newCountry = 'MyNewCountry';
        (new CountryController($this->school))->store([
            'name' => $newCountry,
        ]);

        //Find it
        $countries = (new CountryController($this->school))->index();
        $toDelete = ($this->findCountryByName($newCountry, $countries->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new CountryController($this->school))->destroy($idToDelete);
            $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /** @test */
    public function it_updates_a_country()
    {
        //Create it
        $newCountry = 'MyNewCountry';
        (new CountryController($this->school))->store([
            'name' => $newCountry,
        ]);

        //Find it
        $countries = (new CountryController($this->school))->index();
        $toUpdate = ($this->findCountryByName($newCountry, $countries->toArray()));

        //Update it
        $renameCountry = 'MySpecialCountry';
        $response = (new CountryController($this->school))->update($toUpdate[0], ['name' => $renameCountry]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $countries = (new CountryController($this->school))->index();
        $toDelete = ($this->findCountryByName($renameCountry, $countries->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new CountryController($this->school))->destroy($idToDelete);
            $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /**
     * Find the country by name
     *
     * @param string $name
     * @param array $countries
     * @return array
     */
    private function findCountryByName(string $name, array $countries)
    {
        $matches = [];
        foreach ($countries as $element):
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        endforeach;

        return $matches;
    }
}
