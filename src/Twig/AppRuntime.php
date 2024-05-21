<?php

namespace App\Twig;

use App\Entity\PersonnePhysique;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
  private ?EntityManagerInterface $_em = null;
  private ?Environment $_env = null;
  private ?Request $_request = null;
  private ?FormFactoryInterface $_factory = null;
  private ?TranslatorInterface $_trans = null;
  private ?RouterInterface $_router = null;

  public function __construct(
    RequestStack $requestStack,
    TranslatorInterface $trans,
    EntityManagerInterface $em,
    Environment $env,
    FormFactoryInterface $factory,
    RouterInterface $router
  ) {
    $this
      ->setRequestFromRequestStack($requestStack)
      ->setTranslator($trans)
      ->setEnvironment($env)
      ->setFactory($factory)
      ->setEntityManager($em)
      ->setRouter($router)
    ;
  }

  public function setRouter(RouterInterface $router): self {
    $this->_router = $router;

    return $this;
  }

  public function getRouter(): ?RouterInterface {
    return $this->_router;
  }

  public function setEntityManager(EntityManagerInterface $em): self {
    $this->_em = $em;

    return $this;
  }

  public function getEntityManager(): ?EntityManagerInterface {
    return $this->_em;
  }

  public function setFactory(FormFactoryInterface $factory): self {
    $this->_factory = $factory;

    return $this;
  }

  public function getFactory(): ?FormFactoryInterface {
    return $this->_factory;
  }

  public function setRequestFromRequestStack(RequestStack $requestStack): self {
    $this->_request = $requestStack->getCurrentRequest();

    return $this;
  }

  public function getRequest(): ?Request {
    return $this->_request;
  }

  public function setTranslator(TranslatorInterface $trans): self {
    $this->_trans = $trans;

    return $this;
  }

  public function getTranslator(): ?TranslatorInterface {
    return $this->_trans;
  }

  public function translate(string $message, array $params=[]): string {
    $tmp = $this->getTranslator()->trans($message);
    foreach($params as $key => $value)
      $tmp = str_replace($key,$value, $tmp);
    return $tmp;
  }

  public function emptyUser(): User {

    $personnePhysique = new PersonnePhysique();
    $reflectionClass = new \ReflectionClass(PersonnePhysique::class);
    $reflectionClass->getProperty('id')->setValue($personnePhysique,0);

    $user = new User();
    $reflectionClass = new \ReflectionClass(User::class);
    $reflectionClass->getProperty('id')->setValue($user,0);
    $reflectionClass->getProperty('adresse')->setValue($user,null);
    $reflectionClass->getProperty('personnePhysique')->setValue($user,$personnePhysique);
    $reflectionClass->getProperty('personneMorale')->setValue($user,null);
    return $user;
  }

  public function setEnvironment(Environment $env): self {
    $this->_env = $env;

    return $this;
  }

  public function getEnvironment(): ?Environment {
    return $this->_env;
  }

}
