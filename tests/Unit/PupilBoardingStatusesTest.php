<?php

namespace Tests\Unit;

use Tests\TestCase;
use spkm\isams\School;
use spkm\isams\Wrappers\PupilBoardingStatus;
use Illuminate\Support\Facades\Cache;
use spkm\isams\Controllers\PupilBoardingStatusController;

class PupilBoardingStatusesTest extends TestCase
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
    public function it_returns_all_boarding_statuses_as_a_collection_of_pupilboardingstatus_classes_stored_in_cache()
    {
        $boardingStatus = (new PupilBoardingStatusController($this->school))->index();

        foreach ($boardingStatus as $status):
            $this->assertTrue(is_a($status, PupilBoardingStatus::class));

        $properties = ['id', 'description', 'listType', 'name'];
        foreach ($properties as $property):
                $this->assertTrue(array_key_exists($property, $status));
        endforeach;
        endforeach;

        //$this->assertTrue(Cache::store('file')->has($this->school->getConfigName().'pupilBoardingStatuses.index'));
    }

    /** @test */
    public function it_creates_a_new_boarding_status()
    {
        $response = (new PupilBoardingStatusController($this->school))->store([
            'name' => 'BoardingPerhapsMaybeNot',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
    }

    /** @test */
    public function it_deletes_a_boarding_status()
    {
        //Create it
        $newboardingStatus = 'BoardingPerhapsMaybeNot';
        (new PupilBoardingStatusController($this->school))->store([
            'name' => $newboardingStatus,
        ]);

        //Find it
        $newBoardingStatuses = (new PupilBoardingStatusController($this->school))->index();

        $toDelete = ($this->findBoardingStatusByName($newboardingStatus, $newBoardingStatuses->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new PupilBoardingStatusController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /** @test */
    public function it_updates_a_boarding_status()
    {
        //Create it
        $newboardingStatus = 'BoardingPerhapsMaybeNot';
        (new PupilBoardingStatusController($this->school))->store([
            'name' => $newboardingStatus,
        ]);

        //Find it
        $counties = (new PupilBoardingStatusController($this->school))->index();
        $toUpdate = ($this->findBoardingStatusByName($newboardingStatus, $counties->toArray()));

        //Update it
        $renameBoardingStatus = 'ImASpecialDayBoy';
        $response = (new PupilBoardingStatusController($this->school))->update($toUpdate[0], ['name' => $renameBoardingStatus]);
        $this->assertEquals(200, $response->getStatusCode());

        //Find it again
        $counties = (new PupilBoardingStatusController($this->school))->index();
        $toDelete = ($this->findBoardingStatusByName($renameBoardingStatus, $counties->toArray()));

        //Delete it
        foreach ($toDelete as $idToDelete):
            $response = (new PupilBoardingStatusController($this->school))->destroy($idToDelete);
        $this->assertEquals(200, $response->getStatusCode());
        endforeach;
    }

    /**
     * Find the elements by name
     *
     * @param string $name
     * @param array $boardingStatuses
     * @return array
     */
    private function findBoardingStatusByName(string $name, array $boardingStatuses)
    {
        $matches = [];
        foreach ($boardingStatuses as $element):
            if ($name == $element->name) {
                array_push($matches, $element->id);
            }
        endforeach;

        return $matches;
    }
}
