<?php

namespace themes\clipone\views\ticketing;

use packages\base\HTTP;
use packages\ticketing\contracts\ILabel;
use packages\ticketing\Label;
use function packages\userpanel\url;

/**
 * @phpstan-import-type SelectOptionType from HelperTrait
 */
trait LabelTrait
{
    /**
     * @var Label[]
     */
    private array $allLabels = [];

    /**
     * @return SelectOptionType[]
     */
    public function getStatusForSelect($withPlaceHolder = false): array
    {
        $options = [];

        if ($withPlaceHolder) {
            $options[] = [
                'title' => t('ticketing.choose'),
                'value' => '',
            ];
        }

        $options[] = [
            'title' => t('titles.ticketing.labels.status.active'),
            'value' => Label::ACTIVE,
        ];
        $options[] = [
            'title' => t('titles.ticketing.labels.status.deactive'),
            'value' => Label::DEACTIVE,
        ];

        return $options;
    }

    /**
     * @return Label[]
     */
    public function getAllLabels(): array
    {
        if (!$this->allLabels) {
            $query = new Label();
            $query->where('status', Label::ACTIVE);

            $this->allLabels = $query->get();
        }

        return $this->allLabels;
    }

    /**
     * @return SelectOptionType[]
     */
    public function getLabelsForSelect(): array
    {
        $options = [];

        foreach ($this->getAllLabels() as $label) {
            $options[] = [
                'title' => $label->title,
                'value' => $label->id,
            ];
        }

        return $options;
    }

    public function getLabel(ILabel $label, string $path, bool $deleteable = false): string
    {
        $class = 'label ticket-label';
        $textColor = $this->getLabelTextClass($label->getColor());
        $title = '';

        if ($label->getDescription()) {
            $class .= ' tooltips';
            $title = ' title="'.$label->getDescription().'"';
        }

        return '<span class="'.$class.'"'.$title.' style="background-color: '.$label->getColor().';">
            '.($deleteable ? '<a href="#" class="btn btn-link btn-xs btn-delete '.$textColor.'" data-id="'.$label->getID().'"><i class="fa fa-times"></i></a>' : '').'
            <a href="'.$this->getSearchLink($label, $path).'" class="btn btn-link btn-xs '.$textColor.'">'.$label->getTitle().'</a>
        </span>';
    }

    /**
     * Using Algoritm that used in gitlab-ui.
     *
     * @see https://gitlab.com/gitlab-org/gitlab-ui/-/blob/main/src/components/base/label/label.vue
     */
    private function getLabelTextClass(string $backgroundColor): string
    {
        $color = '';
        $lightColor = $this->rgbFromHex('#FFFFFF');
        $darkColor = $this->rgbFromHex('#1f1e24');

        if ('#' !== substr($backgroundColor, 0, 1)) {
            throw new \InvalidArgumentException('background color is invalid');
        }

        $color = $this->rgbFromHex($backgroundColor);

        $luminance = $this->relativeLuminance($color);
        $lightLuminance = $this->relativeLuminance($lightColor);
        $darkLuminance = $this->relativeLuminance($darkColor);

        $contrastLight = ($lightLuminance + 0.05) / ($luminance + 0.05);
        $contrastDark = ($luminance + 0.05) / ($darkLuminance + 0.05);

        // Using a threshold contrast of 2.4 instead of 3
        // as this will solve weird color combinations in the mid tones
        return ($contrastLight >= 2.4 or $contrastLight > $contrastDark)
          ? 'label-text-light'
          : 'label-text-dark';
    }

    private function rgbFromHex(string $hex): array
    {
        return sscanf($hex, '#%02x%02x%02x');
    }

    private function relativeLuminance(array $rgb)
    {
        // WCAG 2.1 formula: https://www.w3.org/TR/WCAG21/#dfn-relative-luminance
        // -
        // WCAG 3.0 will use APAC
        // Using APAC would be the ultimate goal, but was dismissed by engineering as of now
        // See https://gitlab.com/gitlab-org/gitlab-ui/-/merge_requests/3418#note_1370107090
        return 0.2126 * $this->toSrgb($rgb[0]) + 0.7152 * $this->toSrgb($rgb[1]) + 0.0722 * $this->toSrgb($rgb[2]);
    }

    private function toSrgb($value): float
    {
        $normalized = $value / 255;

        return $normalized <= 0.03928 ? $normalized / 12.92 : (($normalized + 0.055) / 1.055) ** 2.4;
    }

    private function getSearchLink(ILabel $label, string $path): string
    {
        $query = HTTP::$data;

        $query['labels'] = $label->getID();

        return url($path, $query);
    }
}
