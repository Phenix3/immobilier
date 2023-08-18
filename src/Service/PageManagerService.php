<?php
/**
 * Created by PhpStorm.
 * User: IBM-PC
 * Date: 27/06/2020
 * Time: 09:38
 */

namespace App\Service;


use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageManagerService
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(SessionInterface $session, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->translator = $translator;
    }

    public function setVar(string $name, string $value, array $transVars = [])
    {

        $this->session->set(
            $name,
            $this->translator->trans($value, $transVars)
            );

        return $this;
    }

    public function getTitle()
    {
        if ($this->session->has('page_title')) {
            return $this->session->get('page_title');
        }
        throw new \Exception('Page title not found.');
    }

    public function getIcon()
    {
        if ($this->session->has('page_icon')) {
            return $this->session->get('page_icon');
        }
    }

    public function getDescription()
    {
        if ($this->session->has('page_description')) {
            return $this->session->get('page_description');
        }
        return '';
    }
}