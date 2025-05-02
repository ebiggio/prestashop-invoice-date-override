<?php
declare(strict_types = 1);

namespace Ebiggio\InvoiceDateOverride\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\NumberMinMaxFilterType;
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
                'choices'  => $order_state_choices,
                'multiple' => true,
                'expanded' => true
            ])
            ->add('invoice_date_override_on_new_order', SwitchType::class, [
                'label'    => $this->trans(
                    'Override invoice date on new orders',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'help'     => $this->trans(
                    'If enabled, the invoice date will be overridden when new orders are created that match the selected order status. The invoice date will be set to the order creation date.',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'required' => false
            ])
            ->add('invoice_date_override_on_status_change', SwitchType::class, [
                'label'    => $this->trans(
                    'Override invoice date on order status change',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'help'     => $this->trans(
                    'If enabled, the invoice date will be overridden when the order status changes to the selected order status in the previous field. This event will be triggered for each order status change that matches the selected order status. The invoice date will be set to the order status change date.',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'required' => false
            ])
            ->add(
                'invoice_date_override_clear_on_unselected_status_change', SwitchType::class, [
                'label'    => $this->trans(
                    'Clear invoice date when status change to an unselected one',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'help'     => $this->trans(
                    'If enabled, the invoice date will be cleared when the order status changes to a status that is not selected in the previous field.',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'required' => false
            ])
            ->add('invoice_date_override_manual_ignore_previous_date', SwitchType::class, [
                'label'    => $this->trans(
                    'Ignore previous invoice date',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'help'     => $this->trans(
                    'If checked, the manual override will ignore the previous invoice date and update it to the order creation date. Otherwise, the invoice date will be updated to the order creation date only if the invoice date is empty.',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'required' => false
            ])
            ->add('invoice_date_override_manual_order_id_ranges', NumberMinMaxFilterType::class, [
                'label'    => $this->trans(
                    'Limit manual override to orders in this range',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'help'     => $this->trans(
                    'Optionally set the order ID starting and ending range for the manual override (inclusive). This is useful if you want to limit the number of orders that will be processed and avoid timeouts. If you leave the fields empty, all orders with the selected order status will be processed.',
                    'Modules.InvoiceDateOverride.Admin'
                ),
                'required' => false
            ]);
    }
}