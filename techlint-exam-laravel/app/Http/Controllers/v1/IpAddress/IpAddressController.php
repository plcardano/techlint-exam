<?php

namespace App\Http\Controllers\v1\IpAddress;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\IpAddress\StoreIpAddressRequest;
use App\Http\Requests\v1\IpAddress\UpdateIpAddressRequest;
use App\Http\Resources\v1\IpAddress\IpAddressResource;
use App\Models\IpAddress;
use App\Services\v1\IpAddress\IpAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpAddressController extends Controller
{
    protected $ipAddressService;

    public function __construct(IpAddressService $ipAddressService)
    {
        $this->ipAddressService = $ipAddressService;
    }

    /**
     * Display a listing of IP addresses.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $ipAddresses = IpAddress::query();

        $ipAddresses = $ipAddresses->paginate()->appends($request->query());

        return response()->json(
            IpAddressResource::collection($ipAddresses)
                ->response()
                ->getData(true)
        );
    }

    /**
     * Store a newly created IP address.
     *
     * @param StoreIpAddressRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreIpAddressRequest $request)
    {
        $ipAddress = $this->ipAddressService->storeIpAddress($request->validated());

        return response()->json([
            'message' => 'IP address created successfully',
            'data' => $ipAddress,
        ], 201);
    }

    /**
     * Display the specified IP address.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(IpAddress $ipAddress): JsonResponse
    {
        return response()->json([
            'data' => IpAddressResource::make($ipAddress),
        ]);
    }

    /**
     * Update the specified IP address.
     *
     * @param UpdateIpAddressRequest $request
     * @param IpAddress $ipAddress
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateIpAddressRequest $request, IpAddress $ipAddress): JsonResponse
    {
        if (!$this->ipAddressService->canEdit($ipAddress)) {
            return response()->json([
                'message' => 'You do not have permission to edit this IP address',
            ], 403);
        }

        $ipAddress = $this->ipAddressService->updateIpAddress($ipAddress, $request->validated());

        return response()->json([
            'message' => 'IP address updated successfully',
            'data' => $ipAddress,
        ]);
    }

    /**
     * Remove the specified IP address.
     *
     * @param IpAddress $ipAddress
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(IpAddress $ipAddress)
    {
        $this->ipAddressService->deleteIpAddress($ipAddress);

        return response()->json([
            'message' => 'IP address deleted successfully',
        ]);
    }
}
