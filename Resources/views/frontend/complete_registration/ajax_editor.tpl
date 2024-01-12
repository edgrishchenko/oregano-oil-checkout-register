{block name="frontend_addpassword_editor_modal"}
    <div data-register="true" class="panel register--content" style="display: block; width: 100%">
        {block name="frontend_addpassword_editor_modal_title"}
            <div class="panel--title is--underline">
                {s name="addPasswordTitle" namespace="frontend/complete_registration/editor"}{/s}
            </div>
        {/block}
        {block name="frontend_addpassword_editor_modal_body"}
            <div class="panel--body is--wide">
                {$errors.personal}
                {include file="frontend/register/error_message.tpl" error_messages=$errors.personal}
                {block name="frontend_addpassword_editor_modal_form"}
                    <form id="addPassword" class="register--form" name="addPassword" method="post" action="{url controller=completeRegistration action=ajaxSaveRegister}">
                        <div class="register--account-information">
                            {* Password *}
                            {block name='frontend_register_personal_fieldset_input_password'}
                                <div class="register--password">
                                    <input name="register[personal][password]"
                                           type="password"
                                           autocomplete="new-password"
                                           required="required"
                                           aria-required="true"
                                           placeholder="{s name='RegisterPlaceholderPassword' namespace="frontend/register/personal_fieldset"}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
                                           id="register_personal_password"
                                           class="register--field password is--required{if isset($error_flags.password)} has--error{/if}"/>
                                </div>
                            {/block}

                            {* Password confirmation *}
                            {block name='frontend_register_personal_fieldset_input_password_confirm'}
                                {if {config name=doublePasswordValidation}}
                                    <div class="register--passwordconfirm">
                                        <input name="register[personal][passwordConfirmation]"
                                               type="password"
                                               autocomplete="new-password"
                                               aria-required="true"
                                               placeholder="{s name='RegisterPlaceholderPasswordRepeat' namespace="frontend/register/personal_fieldset"}{/s}{s name="RequiredField" namespace="frontend/register/index"}{/s}"
                                               id="register_personal_passwordConfirmation"
                                               class="register--field passwordConfirmation is--required{if isset($error_flags.passwordConfirmation)} has--error{/if}"/>
                                    </div>
                                {/if}
                            {/block}

                            {* Password description *}
                            {block name='frontend_register_personal_fieldset_password_description'}
                                <div class="register--password-description" style="padding-bottom: 0;">
                                    {s name='RegisterInfoPassword' namespace="frontend/register/personal_fieldset"}{/s} {config name=MinPassword} {s name='RegisterInfoPasswordCharacters' namespace="frontend/register/personal_fieldset"}{/s}
                                    <br/>{s name='RegisterInfoPassword2' namespace="frontend/register/personal_fieldset"}{/s}
                                </div>
                            {/block}
                        </div>
                    </form>
                {/block}
            </div>
        {/block}
        {block name="frontend_addpassword_action_buttons"}
            <div class="panel--actions addpassword--form-actions is--wide" style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px">
                {block name="frontend_addpassword_action_button_continue_without"}
                    {block name='frontend_checkout_confirm_submit'}
                        <button type="submit" class="btn is--primary is--large" form="confirm--form" data-preloader-button="true" style="margin: 0">
                            {s name="continueWithoutRegistration" namespace="frontend/complete_registration/editor"}{/s}
                        </button>
                    {/block}
                {/block}

                {block name="frontend_addpassword_action_button_continue_with"}
                    <button type="submit" class="btn is--primary is--large" form="addPassword" data-preloader-button="true" style="margin: 0">
                        {s name="continueWithRegistration" namespace="frontend/complete_registration/editor"}{/s}
                    </button>
                {/block}
            </div>
        {/block}
    </div>
{/block}
