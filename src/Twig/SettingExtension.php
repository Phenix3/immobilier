<?php

namespace App\Twig;

use App\Repository\SettingRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class SettingExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var SettingRepository
     */
    protected $settingRepository;
    /**
     * @var TagAwareCacheInterface
     */
    private $cache;

    public function __construct(SettingRepository $settingRepository, TagAwareCacheInterface $cache)
    {
        $this->settingRepository = $settingRepository;
        $this->cache = $cache;
    }



    public function getFunctions(): array
    {
        return [
            new TwigFunction('setting', [$this, 'getSetting'], ['needs_context' => true, 'is_safe' => ['html']]),
        ];
    }

    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getSettings()
    {
        return $this->cache->get('global_settings', function (ItemInterface $item) {
            $item->tag('global_settings');
            return $this->settingRepository->findAll();
        });
    }

    public function getSetting(array $context, string $name)
    {
        $settings = $context['settings'];
        $setting = array_reduce(array_filter($settings, static function($setting) use ($name) {
            return $setting->getName() === $name;
        }), static function ($key, $setting) use ($name) {
            return $setting->getName() === $name ? $setting:null;
        });
//        dump($setting);
        return $setting ? $setting->getValue() : '';
    }

    public function getGlobals(): array
    {
        return [
            'settings' => $this->getSettings()
        ];
    }
}
