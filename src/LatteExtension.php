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
use Latte\Extension;
use UnexpectedValueException;

class LatteExtension extends Extension
{
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
			'prettyJson' => $this->filterPrettyJson(...),
			'syntaxHighlight' => $this->filterSyntaxHighlight(...),
		];
	}


	protected function filterStatus(
		?bool $status,
	): Html {
		return Html::status($status);
	}


	protected function filterPhone(
		?string $phone,
	): ?string {
		return Format::phoneNumber($phone);
	}


	protected function filterIcon(
		?string $icon,
		bool $fixedWidth = true,
		string ...$classes,
	): ?Html {
		if (!isset($icon)) {
			return null;
		}

		/** @var Html */
		return Html::icon($icon, $fixedWidth)->addClass($classes);
	}


	protected function filterBadge(
		string|LabeledEnum $content,
		string|Color $color = Color::Secondary,
		string $icon = null,
	): Html {
		/** @var Color */
		$color = Color::make($color);

		if ($content instanceof LabeledEnum) {
			return Html::badgeEnum($content);
		}

		return Html::badge($content, $color, $icon);
	}


	/**
	 * @throws UnexpectedValueException
	 */
	protected function filterPrice(
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

		/** @var Html */
		return Html::price((float) $amount, $currency, isColoredBySign: $isColored)->addClass($classes);
	}


	protected function filterPrettyJson(
		mixed $value,
	): string {
		return Json::encode($value, Json::PRETTY);
	}


	protected function filterSyntaxHighlight(
		?string $code,
		?string $lang,
		bool $isBackColored = false,
	): Html {
		$html = Html::el('code');

		if ($isBackColored) {
			$html->addClass('hljs');
		}

		if (!class_exists(Highlighter::class)) {
			return $html->setText($code);
		}

		$highlight = (new Highlighter)->highlight($lang, $code);

		/** @var Html */
		return $html->addClass($highlight->language)
			->setHtml($highlight->value);
	}
}
