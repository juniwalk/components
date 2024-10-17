<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Components;

use Highlight\Highlighter;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Enums\Currency;
use JuniWalk\Utils\Enums\Interfaces\Currency as CurrencyInterface;
use JuniWalk\Utils\Enums\Interfaces\LabeledEnum;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Html;
use JuniWalk\Utils\Json;
use JuniWalk\Utils\Parse;
use JuniWalk\Utils\Strings;
use Latte\ContentType;
use Latte\Extension;
use Latte\Runtime\FilterInfo;
use League\CommonMark\GithubFlavoredMarkdownConverter as MarkdownConverter;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\Translator;
use Stringable;
use UnexpectedValueException;

class LatteExtension extends Extension
{
	protected const LinkPattern = '/\[(?<label>.+)\]\((?<value>.+)\)/iU';

	public function __construct(
		protected readonly Translator $translator,
		protected readonly LinkGenerator $linkGenerator,
	) {
	}


	/**
	 * @return array<string, callable>
	 */
	public function getFilters(): array
	{
		return [
			'status' => $this->filterStatus(...),
			'phone' => $this->filterPhone(...),
			'badge' => $this->filterBadge(...),
			'price' => $this->filterPrice(...),
			'icon' => $this->filterIcon(...),
			'popover' => $this->filterPopover(...),
			'markdown' => $this->filterMarkdown(...),
			'prettyJson' => $this->filterPrettyJson(...),
			'syntaxHighlight' => $this->filterSyntaxHighlight(...),
		];
	}


	protected function filterStatus(
		FilterInfo $info,
		?bool $status,
	): Html {
		$info->contentType = ContentType::Html;
		return Html::status($status);
	}


	protected function filterPhone(
		?string $phone,
	): ?string {
		return Format::phoneNumber($phone);
	}


	protected function filterIcon(
		FilterInfo $info,
		?string $icon,
		bool $fixedWidth = true,
		string ...$classes,
	): ?Html {
		if (!isset($icon)) {
			return null;
		}

		$info->contentType = ContentType::Html;

		/** @var Html */
		return Html::icon($icon, $fixedWidth)->addClass($classes);
	}


	protected function filterBadge(
		FilterInfo $info,
		string|LabeledEnum|null $content,
		string|Color $color = Color::Secondary,
		?string $icon = null,
	): ?Html {
		if (empty($content)) {
			return null;
		}

		if ($content instanceof LabeledEnum) {
			return Html::badgeEnum($content);
		}

		$info->contentType = ContentType::Html;

		return Html::badge($content, Color::make($color), $icon);
	}


	/**
	 * @throws UnexpectedValueException
	 */
	protected function filterPrice(
		FilterInfo $info,
		?float $amount,
		string|CurrencyInterface $currency,
		bool $isColored = true,
		string ...$classes,
	): Html {
		if (is_string($currency) && method_exists(Currency::class, 'remake')) {
			/** @var CurrencyInterface */
			$currency = Currency::remake($currency);
		}

		if (is_string($currency)) {
			throw new UnexpectedValueException('Currency has to be instance of '.CurrencyInterface::class);
		}

		$info->contentType = ContentType::Html;

		/** @var Html */
		return Html::price((float) $amount, $currency, isColoredBySign: $isColored)->addClass($classes);
	}


	protected function filterPrettyJson(
		mixed $value,
	): string {
		return Json::encode($value, Json::PRETTY);
	}


	protected function filterSyntaxHighlight(
		FilterInfo $info,
		?string $code,
		?string $lang,
		bool $isBackColored = false,
	): ?Html {
		if (!class_exists(Highlighter::class)) {
			trigger_error('Missing "scrivo/highlight.php" package', E_USER_WARNING);
			return null;
		}

		$html = Html::el('code');

		if ($isBackColored) {
			$html->addClass('hljs');
		}

		$highlight = (new Highlighter)->highlight($lang ?? 'html', $code ?? '');
		$info->contentType = ContentType::Html;

		/** @var Html */
		return $html->addClass($highlight->language)
			->setHtml($highlight->value);
	}


	protected function filterMarkdown(
		FilterInfo $info,
		string|Stringable $content,
	): ?Html {
		if (!class_exists(MarkdownConverter::class)) {
			trigger_error('Missing "league/commonmark" package', E_USER_WARNING);
			return null;
		}

		$content = (string) $content;
		$matches = Strings::matchAll($content, static::LinkPattern);
		$params = ['allow_unsafe_links' => false];

		foreach ($matches as $match) {
			$value = $this->createLink($match['value'], $match['label'] ?? null) ?? '';
			$content = str_replace($match[0], $value, $content);
		}

		$content = (new MarkdownConverter($params))->convert($content);
		$info->contentType = ContentType::Html;

		/** @var Html */
		return Html::el()->setHtml((string) $content);
	}


	protected function filterPopover(
		FilterInfo $info,
		string|Stringable $content,
		string $title,
		string $icon = 'fa-question-circle',
		string|Color $color = Color::Info,
		string ...$classes,
	): Html {
		$icon = Html::icon($icon, true, Color::make($color));
		$html = Html::el('button type="button" class="btn btn-link p-0 mt-n2"')
			->title($icon.' '.$this->translator->translate($title))
			->data('toggle', 'popover')->data('trigger', 'focus')
			->data('content', $content)
			->tabindex(0);

		$info->contentType = ContentType::Html;

		/** @var Html */
		return $html->setHtml($icon)->addClass($classes);
	}


	protected function createLink(string $link, ?string $label = null): ?string
	{
		try {
			if (!$link = Parse::link($link)) {
				return null;
			}

			$url = $this->linkGenerator->link($link->path, $link->args);

			if ($label === null) {
				return $url;
			}

			$html = Html::el('a')->setHtml($label)
				->setTarget('_blank')
				->setHref($url);

			if ($link->signal === true) {
				$html->addClass('ajax');
			}

		} catch (InvalidLinkException) {
			return null;
		}

		return Strings::replace((string) $html, '/\r?\n/');
	}
}
