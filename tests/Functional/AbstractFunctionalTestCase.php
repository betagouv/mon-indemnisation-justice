<?php

namespace MonIndemnisationJustice\Tests\Functional;

use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\String\Slugger\SluggerInterface;

abstract class AbstractFunctionalTestCase extends PantherTestCase
{
    protected PantherClient $client;
    protected ?string $screenShotDirectory = null;

    protected SluggerInterface $slugger;

    protected int $screenshotIndex = 1;
    protected string $step = 'Préparation';

    protected function setUp(): void
    {
        // See config https://hacks.mozilla.org/2017/12/using-headless-mode-in-firefox/
        $this->client = static::createPantherClient(
            [
                'browser' => PantherTestCase::FIREFOX,
                'env' => [
                    'APP_ENV' => 'test',
                    'BASE_URL' => 'http://127.0.0.1:9080/',
                ],
            ],
        );

        $this->slugger = self::getContainer()->get(SluggerInterface::class);

        $this->screenShotDirectory = sprintf(
            '%s/public/screenshots/%s',
            self::getContainer()->getParameter('kernel.project_dir'),
            static::pathSuffix()
        );
    }

    abstract protected static function pathSuffix(): string;

    public function devices(): array
    {
        return [
            'mobile' => ['mobile', 428, 926],
            'desktop' => ['desktop', 1200, 2000],
        ];
    }

    protected function step(string $label): static
    {
        $this->step = $label;

        return $this;
    }

    protected function clearScreenshots(string $device): void
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists("$this->screenShotDirectory/$device")) {
            $filesystem->remove((new Finder())->files()->name('*.png')->in("$this->screenShotDirectory/$device"));
        }
    }

    protected function screenshot(string $device, ?string $extra = null): static
    {
        $this->client->takeScreenshot(
            sprintf(
                '%s/%s/%s-%s.png',
                $this->screenShotDirectory,
                $device,
                str_pad($this->screenshotIndex++, 3, '0', STR_PAD_LEFT),
                $this->slugger->slug($this->step.($extra ? ' '.$extra : ''))
            )
        );

        return $this;
    }

    // Cas des input 'checkbox‘ ou 'radio':
    protected function checkField(string $legend, string $label, bool $exactMatch = false): static
    {
        $field = $this->getFieldByLabel($label, $legend, $exactMatch);

        if (null === $field->getElement(0)) {
            throw new \LogicException("Aucun élément trouvé pour le champs de label '$legend' > '$label'");
        }

        if ('input' !== $field->getTagName()) {
            throw new \LogicException("L'élément trouvé pour le champs de label '$legend' > '$label' a un type inattendu {$field->getTagName()} (attendu <input>)");
        }

        try {
            $field->click();
        } catch (ElementClickInterceptedException $e) {
            // Dans le cas où le label englobe le champs, on se replie sur le clic sur le <label>
            $this->client->getCrawler()->filter(sprintf('label[for="%s"]', $field->getAttribute('id')))->first()->click();
        }

        return $this;
    }

    protected function setField(string $label, string|bool $value, bool $exactMatch = false): static
    {
        $field = $this->getFieldByLabel($label, exactMatch: $exactMatch);

        if (null === $field->getElement(0)) {
            throw new \LogicException("Aucun élément trouvé pour le champs de label '$label'");
        }

        switch ($field->getTagName()) {
            case 'textarea':
            case 'input':
                if ('checkbox' === $field->getAttribute('type')) {
                    $field->click();
                } else {
                    $field->getElement(0)?->clear();
                    if ('file' === $field->getAttribute('type')) {
                        $field->getElement(0)->setFileDetector(new LocalFileDetector());
                    }

                    $field->sendKeys($value);
                }

                break;

            case 'select':
                $options = $field->filter('option');
                $option = $options->reduce(function (PantherCrawler $o) use ($value) {
                    return trim($o->getText()) === $value;
                })->first();
                $option->click();
                break;
            default:
                throw new \LogicException("L'élément trouvé pour le champs de label '$label' a un type inattendu {$field->getTagName()} (attendu <input>, <select> ou <textarea>)");
        }

        return $this;
    }

    protected function getFieldsByLabel(string $label, bool $exactMatch = false): PantherCrawler
    {
        // Chercher tous les champs (<input>, <select>, <textarea>)
        $crawler = $this->client->getCrawler();

        return $crawler
            ->filter('input,select,textarea')
            ->reduce(function (PantherCrawler $e) use ($label, $crawler, $exactMatch) {
                return $crawler
                    ->filter(sprintf('label[for="%s"]', $e->getAttribute('id')))
                    ->reduce(function (WebDriverElement $l) use ($label, $exactMatch) {
                        return $exactMatch ? trim($l->getText()) === $label : str_contains(trim($l->getText()), $label);
                    })->count() > 0;
            }
            );
    }

    protected function getFieldByLabel(string $label, ?string $legend = null, bool $exactMatch = false): PantherCrawler
    {
        $fields = $this->getFieldsByLabel($label);

        if (null === $legend) {
            return $fields->first();
        }

        $ff = $fields->reduce(function (PantherCrawler $e) use ($legend, $exactMatch) {
            // On remonte jusqu'au plus proche fieldset englobant
            return 1 === $e->ancestors()
                    ->filter('fieldset')
                    ->first()
                    ->filter('legend')
                    ->reduce(
                        // On cherche l'élément <legend> dans le fieldset dont le texte est $legend
                        function (WebDriverElement $l) use ($legend, $exactMatch) {
                            return $exactMatch ? trim($l->getText()) === $legend : str_contains(trim(str_replace('\n', '', $l->getText())), $legend);
                        }
                    )
                    ->count();
        });

        return $ff->first();
    }
}
