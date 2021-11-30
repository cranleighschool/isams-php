<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeePhoto;

class HumanResourcesEmployeePhotoController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain() . '/api/humanresources/employees';
    }

    /**
     * Gets the Current Photo for the Employee.
     *
     * @param  int  $id
     * @param  int  $quality
     * @return EmployeePhoto
     *
     * @throws GuzzleException
     */
    public function getCurrentPhoto(int $id, int $quality = 75): EmployeePhoto
    {
        /**
         * At the moment this package doesn't auto-include Intervention, so we need to check for its existence first.
         */
        if (! method_exists(ImageManagerStatic::class, 'make')) {
            throw new Exception('This method requires Intervention/Image package.', 500);
        }

        try {
            $response = $this->guzzle->request('GET', $this->endpoint . '/' . $id . '/photos/current', ['headers' => $this->getHeaders()]);

            /**
             * Get the Image and Save it to Storage.
             */
            $image = ImageManagerStatic::make($response->getBody()->getContents());
            $data = $image->encode('jpg', $quality);
            $save = Storage::put($id . '.jpg', $data);

            /**
             * Grab the image out of storage and encode it as a Data URL
             * Then Delete the image from Storage. (Like we'd never know it was there!).
             */
            $image = storage_path('app/' . $id . '.jpg');
            $image = ImageManagerStatic::make($image)->encode('data-url');
            Storage::delete($id . '.jpg');
        } catch (RequestException $exception) {
            $image = ['error' => json_decode($exception->getResponse()->getBody()->getContents())];
        }

        /**
         * Return an instance of the EmployeePhoto class.
         */
        return new EmployeePhoto($image);
    }
}
