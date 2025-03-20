<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Context;
use OrderState;

class SettingsFormType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $order_states = OrderState::getOrderStates(Context::getContext()->language->id);
        $order_state_choices = [];

        foreach ($order_states as $order_state) {
            $order_state_choices[$order_state['name']] = $order_state['id_order_state'];
        }

        $builder
            ->add('invoice_date_override_order_status', ChoiceType::class, [
                'label'    => $this->trans(
                    'Order status that will trigger the override',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'help'     => $this->trans(
                    'Select the order status that will trigger the invoice date override.',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'required' => true,
                'choices' => [
                    $order_state_choices
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
    }
}