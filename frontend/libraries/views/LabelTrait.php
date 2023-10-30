<?php

namespace themes\clipone\views\ticketing;

use packages\ticketing\Label;

/**
 * @phpstan-import-type SelectOptionType from HelperTrait
 */
trait LabelTrait
{
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

    public function getLabel(string $title, string $backgroundColor): string
    {
        return '<span class="label" style="background-color: '.$backgroundColor.';"><span class="'.$this->getLabelTextClass($backgroundColor).'">'.$title.'</span></span>';
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
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return [$r, $g, $b];
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
}
