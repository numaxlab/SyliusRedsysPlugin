<?php

namespace Eclyptox\SyliusRedsysPlugin\Payum\Action;

use Eclyptox\SyliusRedsysPlugin\Payum\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        $suffix = getenv('ORDER_SUFFIX') ?? '';

        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details->defaults(
            array(
                'Ds_Merchant_Amount' => $payment->getTotalAmount(),
                'Ds_Merchant_Order' => $this->api->ensureCorrectOrderNumber($payment->getNumber().$suffix),
                'Ds_Merchant_MerchantCode' => $this->api->getMerchantCode(),
                'Ds_Merchant_Currency' => $this->api->getISO4127($payment->getCurrencyCode()),
                'Ds_Merchant_TransactionType' => Api::TRANSACTIONTYPE_AUTHORIZATION,
                'Ds_Merchant_Terminal' => $this->api->getMerchantTerminalCode(),
                'Ds_Merchant_ConsumerLanguage' => $this->api->getConsumerLanguageCode(),
                'Ds_Merchant_PayMethods' => $this->api->getPayMethodsCode(),
            )
        );

        $request->setResult((array)$details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            'array' == $request->getTo() &&
            $request->getSource() instanceof PaymentInterface;
    }
}
