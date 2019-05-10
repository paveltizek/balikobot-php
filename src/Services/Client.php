<?php

namespace Inspirum\Balikobot\Services;

use DateTime;
use Inspirum\Balikobot\Contracts\RequesterInterface;
use Inspirum\Balikobot\Definitions\Country;
use Inspirum\Balikobot\Definitions\Request;
use Inspirum\Balikobot\Exceptions\BadRequestException;

class Client
{
    /**
     * API requester.
     *
     * @var \Inspirum\Balikobot\Contracts\RequesterInterface
     */
    private $requester;

    /**
     * Balikobot API client.
     *
     * @param \Inspirum\Balikobot\Contracts\RequesterInterface $requester
     */
    public function __construct(RequesterInterface $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Add package(s) to the Balikobot.
     *
     * @param string $shipper
     * @param array  $packages
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function addPackages(string $shipper, array $packages): array
    {
        $response = $this->requester->call('v1', $shipper, Request::ADD, $packages);

        if (isset($response[0]['package_id']) === false) {
            throw new BadRequestException($response);
        }

//        unset($response['labels_url']);
        unset($response['status']);

        return $response;
    }

    /**
     * Drops a package from the Balikobot. The package must be not ordered.
     *
     * @param string $shipper
     * @param int    $packageId
     *
     * @return void
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function dropPackage(string $shipper, int $packageId): void
    {
        $this->dropPackages($shipper, [$packageId]);
    }

    /**
     * Drops a package from the Balikobot. The package must be not ordered.
     *
     * @param string $shipper
     * @param array  $packageIds
     *
     * @return void
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function dropPackages(string $shipper, array $packageIds): void
    {
        $data = [];

        foreach ($packageIds as $packageId) {
            $data[] = ['id' => $packageId];
        }

        if (count($data) === 0) {
            return;
        }

        $this->requester->call('v1', $shipper, Request::DROP, $data);
    }

    /**
     * Tracks a package
     *
     * @param string $shipper
     * @param string $carrierId
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function trackPackage(string $shipper, string $carrierId): array
    {
        $data = [
            0 => [
                'id' => $carrierId,
            ],
        ];

        $response = $this->requester->call('v2', $shipper, Request::TRACK, $data);

        if (empty($response[0])) {
            throw new BadRequestException($response);
        }

        return $response[0];
    }

    /**
     * Tracks a package, get the last info
     *
     * @param string $shipper
     * @param string $carrierId
     *
     * @return array
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function trackPackageLastStatus(string $shipper, string $carrierId): array
    {
        $data = [
            0 => [
                'id' => $carrierId,
            ],
        ];

        $response = $this->requester->call('v1', $shipper, Request::TRACK_STATUS, $data, false);

        if (empty($response[0])) {
            throw new BadRequestException($response);
        }

        if (isset($response[0]['status']) && ((int) $response[0]['status']) !== 200) {
            throw new BadRequestException($response);
        }

        $status = [
            'name'      => $response[0]['status_text'],
            'status_id' => $response[0]['status_id'],
            'date'      => null,
        ];

        return $status;
    }

    /**
     * Returns packages from the front (not ordered) for given shipper
     *
     * @param string $shipper
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getOverview(string $shipper): array
    {
        $response = $this->requester->call('v1', $shipper, Request::OVERVIEW, [], false);

        return $response;
    }

    /**
     * Gets labels
     *
     * @param string $shipper
     * @param array  $packageIds
     *
     * @return string
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getLabels(string $shipper, array $packageIds): string
    {
        $data = [
            'package_ids' => $packageIds,
        ];

        $response = $this->requester->call('v1', $shipper, Request::LABELS, $data);

        return $response['labels_url'];
    }

    /**
     * Gets complete information about a package
     *
     * @param string $shipper
     * @param int    $packageId
     *
     * @return array
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getPackageInfo(string $shipper, int $packageId): array
    {
        $response = $this->requester->call('v1', $shipper, Request::PACKAGE . '/' . $packageId, [], false);

        return $response;
    }

    /**
     * Order shipment for packages.
     *
     * @param string         $shipper
     * @param array          $packageIds
     * @param \DateTime|null $date
     * @param string|null    $note
     *
     * @return array
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function orderShipment(string $shipper, array $packageIds, DateTime $date = null, string $note = null): array
    {
        $data = [
            'package_ids' => $packageIds,
            'date'        => $date ? $date->format('Y-m-d') : null,
            'note'        => $note,
        ];

        $response = $this->requester->call('v1', $shipper, Request::ORDER, $data);

        unset($response['status']);

        return $response;
    }

    /**
     * Get order details.
     *
     * @param string $shipper
     * @param int    $orderId
     *
     * @return array
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getOrder(string $shipper, int $orderId): array
    {
        $response = $this->requester->call('v1', $shipper, Request::ORDER_VIEW . '/' . $orderId, [], false);

        unset($response['status']);

        return $response;
    }

    /**
     * Order pickup for packages.
     *
     * @param string      $shipper
     * @param \DateTime   $dateFrom
     * @param \DateTime   $dateTo
     * @param float       $weight
     * @param int         $packageCount
     * @param string|null $message
     *
     * @return void
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function orderPickup(
        string $shipper,
        DateTime $dateFrom,
        DateTime $dateTo,
        float $weight,
        int $packageCount,
        string $message = null
    ): void {
        $data = [
            'date'          => $dateFrom->format('Y-m-d'),
            'time_from'     => $dateFrom->format('H:s'),
            'time_to'       => $dateTo->format('H:s'),
            'weight'        => $weight,
            'package_count' => $packageCount,
            'message'       => $message,
        ];

        $this->requester->call('v1', $shipper, Request::ORDER_PICKUP, $data);
    }

    /**
     * Returns available services for the given shipper
     *
     * @param string $shipper
     *
     * @return string[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getServices(string $shipper): array
    {
        $response = $this->requester->call('v1', $shipper, Request::SERVICES);

        if (isset($response['service_types']) === false) {
            return [];
        }

        return $response['service_types'];
    }

    /**
     * Returns available manipulation units for the given shipper
     *
     * @param string $shipper
     *
     * @return string[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getManipulationUnits(string $shipper): array
    {
        $response = $this->requester->call('v1', $shipper, Request::MANIPULATION_UNITS);

        if ($response['units'] === null) {
            return [];
        }

        $units = [];

        foreach ($response['units'] as $item) {
            $units[$item['code']] = $item['name'];
        }

        return $units;
    }

    /**
     * Returns available branches for the given shipper and its service
     * Full branches instead branches request.
     *
     * @param string $shipper
     * @param string $service
     * @param bool   $fullData
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getBranches(string $shipper, string $service = null, bool $fullData = false): array
    {
        $request = $fullData ? Request::FULL_BRANCHES : Request::BRANCHES;

        $response = $this->requester->call('v1', $shipper, $request . '/' . $service);

        if ($response['branches'] === null) {
            return [];
        }

        return $response['branches'];
    }

    /**
     * Returns available branches for the given shipper in given location
     *
     * @param string      $shipper
     * @param string      $country
     * @param string      $city
     * @param string|null $postcode
     * @param string|null $street
     * @param int|null    $maxResults
     * @param float|null  $radius
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getBranchesForLocation(
        string $shipper,
        string $country,
        string $city,
        string $postcode = null,
        string $street = null,
        int $maxResults = null,
        float $radius = null
    ): array {
        Country::validateCode($country);

        $data = [
            'country'     => $country,
            'city'        => $city,
            'zip'         => $postcode,
            'street'      => $street,
            'max_results' => $maxResults,
            'radius'      => $radius,
        ];

        $data = array_filter($data);

        $response = $this->requester->call('v1', $shipper, Request::BRANCH_LOCATOR, $data);

        if ($response['branches'] === null) {
            return [];
        }

        return $response['branches'];
    }

    /**
     * Returns list of countries where service with cash-on-delivery payment type is available in
     *
     * @param string $shipper
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getCodCountries(string $shipper): array
    {
        $response = $this->requester->call('v1', $shipper, Request::CASH_ON_DELIVERY_COUNTRIES);

        if ($response['service_types'] === null) {
            return [];
        }

        $services = [];

        foreach ($response['service_types'] as $item) {
            $services[$item['service_type']] = $item['cod_countries'];
        }

        return $services;
    }

    /**
     * Returns list of countries where service is available in
     *
     * @param string $shipper
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getCountries(string $shipper): array
    {
        $response = $this->requester->call('v1', $shipper, Request::COUNTRIES);

        if ($response['service_types'] === null) {
            return [];
        }

        $services = [];

        foreach ($response['service_types'] as $item) {
            $services[$item['service_type']] = $item['countries'];
        }

        return $services;
    }

    /**
     * Returns available branches for the given shipper and its service
     *
     * @param string      $shipper
     * @param string      $service
     * @param string|null $country
     *
     * @return array[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getPostCodes(string $shipper, string $service, string $country = null): array
    {
        if ($country !== null) {
            Country::validateCode($country);

            $urlPath = $service . '/' . $country;
        } else {
            $urlPath = $service;
        }

        $response = $this->requester->call('v1', $shipper, Request::ZIP_CODES . '/' . $urlPath);

        if ($response['zip_codes'] === null) {
            return [];
        }

        $country   = $response['country'] ?? $country;
        $postCodes = [];

        foreach ($response['zip_codes'] as $postCode) {
            $postCodes[] = [
                'postcode'     => $postCode['zip'] ?? ($postCode['zip_start'] ?? null),
                'postcode_end' => $postCode['zip_end'] ?? null,
                'city'         => $postCode['city'] ?? null,
                'country'      => $postCode['country'] ?? $country,
                '1B'           => (bool) ($postCode['1B'] ?? false),
            ];
        }

        return $postCodes;
    }

    /**
     * Check package(s) data.
     *
     * @param string $shipper
     * @param array  $packages
     *
     * @return void
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function checkPackages(string $shipper, array $packages): void
    {
        $this->requester->call('v1', $shipper, Request::CHECK, $packages);
    }

    /**
     * Returns available manipulation units for the given shipper
     *
     * @param string $shipper
     *
     * @return string[]
     *
     * @throws \Inspirum\Balikobot\Contracts\ExceptionInterface
     */
    public function getAdrUnits(string $shipper): array
    {
        $response = $this->requester->call('v1', $shipper, Request::ADR_UNITS);

        if ($response['units'] === null) {
            return [];
        }

        $units = [];

        foreach ($response['units'] as $item) {
            $units[$item['code']] = $item['name'];
        }

        return $units;
    }
}
