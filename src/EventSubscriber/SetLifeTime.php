<?php
namespace Drupal\remember_me\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;


class SetLifeTime implements EventSubscriberInterface {

	public function __construct() {
	    $this->account = \Drupal::currentUser();
	}
	public function checkAuthSession(GetResponseEvent $event) {
		if($this->account->id() && $this->account->isAuthenticated()){
			$userData = \Drupal::service('user.data');
			$remember_me_data = $userData->get('remember_me', $this->account->id(), 'UserKeys');
			$config = \Drupal::config('remember_me.settings');
			$remember_managed = $config->get('remember_me_managed');
			$cookie_lifetime = $config->get('remember_me_lifetime');
			if (!empty($remember_me_data) && $remember_managed == 1) {
				// $session_manager = \Drupal::service('session_manager');
				// $session_manager->save(FALSE);
				// session_write_close();
				// $session_manager->save(TRUE);
				ini_set('session.cookie_lifetime', $cookie_lifetime);
			  	if ($cookie_lifetime > 0) {
    				ini_set('session.gc_maxlifetime', $cookie_lifetime);
  				}
				// $session_manager->start();
				// $session_manager->isStarted();
			}
		}
	}
	public static function getSubscribedEvents() {
	    $events[KernelEvents::REQUEST][] = array('checkAuthSession');
	    return $events;
  	}
}