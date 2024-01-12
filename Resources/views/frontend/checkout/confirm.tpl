{extends file="parent:frontend/checkout/confirm.tpl"}

{block name='frontend_checkout_confirm_submit'}
    {if !$sOneTimeAccount}
        {$smarty.block.parent}
    {else}
        {if $sPayment.embediframe || $sPayment.action}
            <a href="{url controller=completeRegistration action=ajaxEditor sTarget=checkout sTargetAction=confirm}"
               id="confirm-order" class="btn is--primary is--large right is--icon-right">
                {s name='ConfirmDoPayment'}{/s}<i class="icon--arrow-right"></i>
            </a>
        {else}
            <a href="{url controller=completeRegistration action=ajaxEditor sTarget=checkout sTargetAction=confirm}"
               id="confirm-order" class="btn is--primary is--large right is--icon-right">
                {s name='ConfirmActionSubmit'}{/s}<i class="icon--arrow-right"></i>
            </a>
        {/if}

    {/if}
{/block}

