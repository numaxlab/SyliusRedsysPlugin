<?php

namespace Eclyptox\SyliusRedsysPlugin\Payum\Action;

use ArrayAccess;
use Eclyptox\SyliusRedsysPlugin\Payum\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;

class NotifyAction implements ApiAwareInterface, ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (!array_key_exists('Ds_Signature', $httpRequest->request) || (null === $httpRequest->request['Ds_Signature'])) {
            throw new HttpResponse('The notification is invalid', 400);
        }

        if (!array_key_exists('Ds_MerchantParameters', $httpRequest->request) || (null === $httpRequest->request['Ds_MerchantParameters'])) {
            throw new HttpResponse('The notification is invalid', 400);
        }

        if (false == $this->api->validateNotificationSignature($httpRequest->request)) {
            throw new HttpResponse('The notification is invalid', 400);
        }

        // After migrating to sha256, DS_Response param is not present in the
        // post request sent by the bank. Instead, bank sends an encoded string
        //  our gateway needs to decode.
        // Once this is decoded we need to add this info to the details among
        // with the $httpRequest->request part

        $details->replace(
            ArrayObject::ensureArrayObject(
                json_decode(base64_decode(strtr($httpRequest->request['Ds_MerchantParameters'], '-_', '+/')))
            )->toUnsafeArray() +
            $httpRequest->request
        );

        throw new HttpResponse('', 200);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
