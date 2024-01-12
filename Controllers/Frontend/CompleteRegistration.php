<?php declare(strict_types=1);

use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Models\Customer\Customer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class Shopware_Controllers_Frontend_CompleteRegistration extends Enlight_Controller_Action
{
    public function ajaxEditorAction()
    {

    }

    public function ajaxSaveRegisterAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $userId = $this->get('session')->get('sUserId');
        $customer = $this->get('models')->find(Customer::class, $userId);
        $customer->setAccountMode(Customer::ACCOUNT_MODE_CUSTOMER);
        $password = $this->Request()->getPost('register')['personal']['password'];
        $customer->setPassword($password);

        /** @var RegisterServiceInterface $registerService */
        $registerService = $this->get('shopware_account.register_service');

        $context = $this->get('shopware_storefront.context_service')->getShopContext();
        $shop = $context->getShop();

        $customerForm = $this->createCustomerForm([
            'customer_type' => $customer->getCustomerType(),
            'salutation' => $customer->getSalutation(),
            'firstname' => $customer->getFirstname(),
            'lastname' => $customer->getLastname(),
            'accountmode' => $customer->getAccountMode(),
            'email' => $customer->getEmail(),
            'emailConfirmation' => $customer->getEmail(),
            'password' => $this->Request()->getPost('register')['personal']['password'],
            'passwordConfirmation' => $this->Request()->getPost('register')['personal']['password']
        ]);

        $errors = [
            'personal' => $this->getFormErrors($customerForm)
        ];

        $errors['occurred'] = (
            !empty($errors['personal'])
        );

        if ($errors['occurred']) {
            $this->handleRegisterError($errors);

            return;
        }

        $registerService->register(
            $shop,
            $customer,
            $customer->getDefaultBillingAddress(),
            $customer->getDefaultShippingAddress()
        );

        $this->saveRegisterSuccess($customer);

        $response['success'] = empty($response['errors']);

        $this->Response()->headers->set('content-type', 'application/json', true);
        $this->Response()->setContent(json_encode($response));
    }

    private function saveRegisterSuccess(Customer $customer): void
    {
        /** @var Enlight_Event_EventManager $eventManager */
        $eventManager = $this->get('events');

        $this->writeSession($customer);
        $this->loginCustomer($customer);

        if ($customer->getAccountMode() == Customer::ACCOUNT_MODE_CUSTOMER) {
            $this->sendRegistrationMail($customer);
        }

        $eventManager->notify(
            'Shopware_Modules_Admin_SaveRegister_Successful',
            [
                'id' => $customer->getId(),
                'billingID' => $customer->getDefaultBillingAddress()->getId(),
                'shippingID' => $customer->getDefaultShippingAddress()->getId(),
            ]
        );
    }

    private function writeSession(Customer $customer): void
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->get('session');
        $session->offsetSet('sOneTimeAccount', false);
        $session->offsetSet('sRegisterFinished', true);

        $session->offsetSet('sCountry', $customer->getDefaultBillingAddress()->getCountry()->getId());
    }

    private function loginCustomer(Customer $customer): void
    {
        $this->front->Request()->setPost('email', $customer->getEmail());
        $this->front->Request()->setPost('passwordMD5', $customer->getPassword());
        Shopware()->Modules()->Admin()->sLogin(true);
    }

    private function sendRegistrationMail(Customer $customer): void
    {
        try {
            Shopware()->Modules()->Admin()->sSaveRegisterSendConfirmation($customer->getEmail());
        } catch (\Exception $e) {
            $message = sprintf('Could not send user registration email to address %s', $customer->getEmail());
            $this->get('corelogger')->error($message, ['exception' => $e->getMessage()]);
        }
    }

    private function createCustomerForm(array $data): Form
    {
        $customer = new Customer();
        $form = $this->createForm(PersonalFormType::class, $customer);
        $form->submit($data);

        return $form;
    }


    private function getFormErrors(FormInterface $form): array
    {
        if ($form->isValid()) {
            return [];
        }

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $this->View()->fetch('string:' . $error->getMessage());
        }
        unset($errors['dpacheckbox']);

        return $errors;
    }

    private function handleRegisterError(array $errors): void
    {
        $this->View()->assign('errors', $errors);
        $this->forward('ajaxEditor');
    }
}