<?php

namespace spkm\isams\Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\AdmissionStatus;
use spkm\isams\Controllers\AdmissionStatusController;

class AdmissionStatusTest extends TestCase
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
    public function it_returns_all_admission_statuses_as_a_collection_of_admissionstatus_classes_stored_in_cache()
    {
        $admissionStatuses = (new AdmissionStatusController($this->school))->index();

        foreach ($admissionStatuses as $admissionStatus):
            $this->assertTrue(is_a($admissionStatus, AdmissionStatus::class));

        $properties = ['id', 'description', 'listType', 'name'];
        foreach ($properties as $property):
                $this->assertTrue(array_key_exists($property, $admissionStatus));
        endforeach;
        endforeach;

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'admissionStatuses.index'));
    }

    /** @test */
    public function it_creates_a_new_admission_status()
    {
        $response = (new AdmissionStatusController($this->school))->store([
            'name' => 'MyNewAdmissionStatus',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_an_admission_status()
    {
        //Create it
        $newAdmissionStatus = 'MyNewAdmissionStatus';
        (new AdmissionStatusController($this->school))->store([
            'name' => $newAdmissionStatus,
        ]);

        //Find it
        $admissionStatuses = (new AdmissionStatusController($this->school))->index();
        $toDelete = ($this->findAdmissionStatusByName($newAdmissionStatus, $admissionStatuses->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):

            $response = (new AdmissionStatusController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /** @test */
    public function it_updates_a_admission_status()
    {
        //Create it
        $newAdmissionStatus = 'MyNewAdmissionStatus';
        (new AdmissionStatusController($this->school))->store([
            'name' => $newAdmissionStatus,
        ]);

        //Find it
        $admissionStatuses = (new AdmissionStatusController($this->school))->index();
        $toUpdate = ($this->findAdmissionStatusByName($newAdmissionStatus, $admissionStatuses->toArray()));

        //Update it
        $renameAdmissionStatus = 'MySpecialAdmissionStatus';
        $response = (new AdmissionStatusController($this->school))->update($toUpdate[0], ['name' => $renameAdmissionStatus]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $admissionStatuses = (new AdmissionStatusController($this->school))->index();
        $toDelete = ($this->findAdmissionStatusByName($renameAdmissionStatus, $admissionStatuses->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new AdmissionStatusController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /**
     * Find the elements by name
     *
     * @param string $name
     * @param array $admissionStatuses
     * @return array
     */
    private function findAdmissionStatusByName(string $name, array $admissionStatuses)
    {
        $matches = [];
        foreach ($admissionStatuses as $element):
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        endforeach;

        return $matches;
    }
}
