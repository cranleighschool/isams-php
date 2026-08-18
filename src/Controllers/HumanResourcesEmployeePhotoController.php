<?php

namespace spkm\isams\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use spkm\isams\Endpoint;
use spkm\isams\Wrappers\EmployeePhoto;

class HumanResourcesEmployeePhotoController extends Endpoint
{
    /**
     * Set the URL the request is made to.
     *
     *
     * @throws Exception
     */
    protected function setEndpoint(): void
    {
        $this->endpoint = $this->getDomain().'/api/humanresources/employees';
    }

    /**
     * Gets the Current Photo for the Employee.
     *
     *
     * @throws GuzzleException
     */
    public function getCurrentPhoto(int $id, int $quality = 75): EmployeePhoto
    {
        /**
         * At the moment this package doesn't auto-include Intervention, so we need to check for its existence first.
         */
        if (! class_exists(ImageManager::class)) {
            throw new Exception('This method requires Intervention/Image package.', 500);
        }

        try {
            $response = $this->guzzle->request('GET', $this->endpoint.'/'.$id.'/photos/current', ['headers' => $this->getHeaders()]);

            $manager = new ImageManager($this->resolveImageDriver());

            /**
             * Get the Image and Save it to Storage.
             */
            $data = $manager->decode($response->getBody()->getContents())->encode(new JpegEncoder(quality: $quality));
            $save = Storage::put($id.'.jpg', (string) $data);

            /**
             * Grab the image out of storage and encode it as a Data URL
             * Then Delete the image from Storage. (Like we'd never know it was there!).
             */
            $image = (string) $manager->decode(storage_path('app/'.$id.'.jpg'))->encode(new JpegEncoder)->toDataUri();
            Storage::delete($id.'.jpg');
        } catch (RequestException $exception) {
            $image = ['error' => json_decode($exception->getResponse()->getBody()->getContents())];
        }

        /**
         * Return an instance of the EmployeePhoto class.
         */
        return new EmployeePhoto($image);
    }

    /**
     * Resolve the Intervention Image driver to use. Defaults to auto-detecting whichever of
     * Imagick or GD is installed, but can be pinned via the "isams.image.driver" config value
     * (ISAMS_IMAGE_DRIVER env var) for consumers who need a specific driver.
     *
     * @throws Exception
     */
    protected function resolveImageDriver(): DriverInterface
    {
        return match (config('isams.image.driver', 'auto')) {
            'gd' => new GdDriver,
            'imagick' => new ImagickDriver,
            default => match (true) {
                extension_loaded('imagick') => new ImagickDriver,
                extension_loaded('gd') => new GdDriver,
                default => throw new Exception('This method requires either the GD or Imagick PHP extension to be installed.', 500),
            },
        };
    }
}
