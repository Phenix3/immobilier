<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 21/06/2019
 * Time: 12:00
 */

namespace App\Listener;

use App\Entity\Image;
use App\Entity\Property;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageCacheSubscriber implements EventSubscriber {

	/**
	 * @var CacheManager
	 */
	private $cacheManager;
	/**
	 * @var UploaderHelper
	 */
	private $uploaderHelper;

	public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper) {

		$this->cacheManager = $cacheManager;
		$this->uploaderHelper = $uploaderHelper;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return string[]
	 */
	public function getSubscribedEvents() {
		return [
			'preRemove',
			'preUpdate',
		];
	}

	public function preRemove(LifecycleEventArgs $args) {
		$entity = $args->getEntity();
		if ((!$entity instanceof Property) && (!$entity instanceof Image)) {
			return;
		}
//        dump($entity);

		$this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));

	}

	public function preUpdate(PreUpdateEventArgs $args) {
		$entity = $args->getEntity();
//        dump($entity);
		if ((!$entity instanceof Property) && (!$entity instanceof Image)) {
			return;
		}
		if ($entity->getImageFile() instanceof UploadedFile) {
			if ($this->uploaderHelper->asset($entity, 'imageFile')) {
				$this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
			}
		}
	}

}