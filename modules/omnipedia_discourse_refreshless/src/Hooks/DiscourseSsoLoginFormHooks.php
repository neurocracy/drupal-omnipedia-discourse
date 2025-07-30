<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse_refreshless\Hooks;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Drupal\hux\Attribute\Alter;

/**
 * Log in form hooks for Discourse SSO when using RefreshLess.
 */
class DiscourseSsoLoginFormHooks {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirectDesitination
   *   The Drupal redirect destition service.
   */
  public function __construct(
    protected readonly RedirectDestinationInterface $redirectDesitination,
  ) {}

  /**
   * Determine if the current destination points to the Discourse SSO route.
   *
   * @return boolean
   */
  protected function isDestinationDiscourseSso(): bool {

    try {

      $destination = Url::fromUserInput($this->redirectDesitination->get());

    } catch (\Exception $exception) {

      return false;

    }

    return (
      $destination->isRouted() &&
      $destination->getRouteName() === 'discourse_sso.sso'
    );

  }

  #[Alter('form_user_login_form')]
  #[Alter('form_tfa_entry_form')]
  /**
   * Disable RefreshLess on the user log in and TFA entry forms.
   *
   * @param array $form
   *   Nested array of form elements that comprise the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The current state of the form.
   *
   * @param string $formId
   *   The ID of the current form.
   *
   * @see hook_form_FORM_ID_alter
   *
   * @see https://gitlab.com/neurocracy/omnipedia/modules/omnipedia-discourse/-/issues/15
   */
  public function disableRefreshless(
    array &$form, FormStateInterface $formState, string $formId,
  ): void {

    if ($this->isDestinationDiscourseSso() === false) {
      return;
    }

    $form['#attributes']['data-turbo'] = 'false';

  }

}
