<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

/**
 * Handles back office settings page.
 */
class BackOfficeSettings extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $formHandler = $this->get('ebiggio.invoice_date_override.form.settings_form_handler');
        $configurationForm = $formHandler->getForm();
        $configurationForm->handleRequest($request);

        if ($configurationForm->isSubmitted() && $configurationForm->isValid()) {
            $formErrors = $formHandler->save($configurationForm->getData());

            if (empty($formErrors)) {
                $this->addFlash('success', $this->trans('Settings updated successfully.', 'Admin.Notifications.Success'));

                return $this->redirectToRoute('invoice_date_override_settings');
            }

            foreach ($formErrors as $key => $error) {
                $configurationForm->get($key)->addError(new FormError($error));
            }

            $this->flashErrors([$this->trans(
                'An error occurred while updating settings. Please review the form.',
                'Admin.Notifications.Error'
            )]);
        }

        return $this->render('@Modules/invoice_date_override/views/templates/admin/settings.html.twig', [
            'SettingsForm'  => $configurationForm->createView(),
            'layoutTitle'   => $this->trans('Invoice Date Override', 'Modules.InvoiceDateOverride.Admin'),
            'enableSidebar' => true,
            'help_link'     => $this->generateSidebarLink('BackOfficeSettings'),
        ]);
    }
}