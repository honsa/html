<?php
namespace Yiisoft\Html;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Json\Json;

/**
 * Html provides a set of static methods for generating commonly used HTML tags.
 *
 * Nearly all of the methods in this class allow setting additional HTML attributes for the HTML tags they generate.
 * You can specify, for example, `class`, `style` or `id` for an HTML element using the `$options` parameter. See the
 * documentation of the {@see tag()} method for more details.
 */
final class Html
{
    /**
     * @var string Regular expression used for attribute name validation.
     */
    public static $attributeRegex = '/(^|.*\])([\w\.\+]+)(\[.*|$)/u';

    /**
     * @var array list of void elements (element name => 1)
     * @see http://www.w3.org/TR/html-markup/syntax.html#void-element
     */
    public static $voidElements = [
        'area' => 1,
        'base' => 1,
        'br' => 1,
        'col' => 1,
        'command' => 1,
        'embed' => 1,
        'hr' => 1,
        'img' => 1,
        'input' => 1,
        'keygen' => 1,
        'link' => 1,
        'meta' => 1,
        'param' => 1,
        'source' => 1,
        'track' => 1,
        'wbr' => 1,
    ];

    /**
     * @var array the preferred order of attributes in a tag. This mainly affects the order of the attributes that are
     * rendered by {@see renderTagAttributes()}.
     */
    public static $attributeOrder = [
        'type',
        'id',
        'class',
        'name',
        'value',

        'href',
        'src',
        'srcset',
        'form',
        'action',
        'method',

        'selected',
        'checked',
        'readonly',
        'disabled',
        'multiple',

        'size',
        'maxlength',
        'width',
        'height',
        'rows',
        'cols',

        'alt',
        'title',
        'rel',
        'media',
    ];

    /**
     * @var array list of tag attributes that should be specially handled when their values are of array type.
     * In particular, if the value of the `data` attribute is `['name' => 'xyz', 'age' => 13]`, two attributes will be
     * generated instead of one: `data-name="xyz" data-age="13"`.
     */
    public static $dataAttributes = ['data', 'data-ng', 'ng'];


    /**
     * Encodes special characters into HTML entities.
     *
     * @param string|null $content the content to be encoded
     * @return string the encoded content
     * @see decode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function encode(?string $content): string
    {
        return htmlspecialchars(
            $content,
            ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
            ini_get('default_charset'),
            false
        );
    }

    /**
     * Decodes special HTML entities back to the corresponding characters. This is the opposite of {@see encode()}.
     *
     * @param string $content the content to be decoded
     * @return string the decoded content
     * @see encode()
     * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function decode(string $content): string
    {
        return htmlspecialchars_decode($content, ENT_QUOTES);
    }

    /**
     * Generates a complete HTML tag.
     *
     * @param string|bool|null $name the tag name. If $name is `null` or `false`, the corresponding content will be
     * rendered without any tag.
     * @param string $content the content to be enclosed between the start and end tags. It will not be HTML-encoded.
     * If this is coming from end users, you should consider {@see encode()} it to prevent XSS attacks.
     * @param array $options the HTML tag attributes (HTML options) in terms of name-value pairs. These will be
     * rendered as the attributes of the resulting tag. The values will be HTML-encoded using {@see encode()}. If a
     * value is null, the corresponding attribute will not be rendered.
     *
     * For example when using `['class' => 'my-class', 'target' => '_blank', 'value' => null]` it will result in the
     * HTML attributes rendered like this: `class="my-class" target="_blank"`.
     *
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated HTML tag
     * @see beginTag()
     * @see endTag()
     */
    public static function tag($name, string $content = '', array $options = []): string
    {
        if ($name === null || $name === false) {
            return $content;
        }
        $html = '<' .$name . static::renderTagAttributes($options) . '>';
        return isset(static::$voidElements[strtolower($name)]) ? $html : "$html$content</$name>";
    }

    /**
     * Generates a start tag.
     *
     * @param string|bool|null $name the tag name. If $name is `null` or `false`, the corresponding content will be
     * rendered without any tag.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated start tag
     * @see endTag()
     * @see tag()
     */
    public static function beginTag($name, array $options = []): string
    {
        if ($name === null || $name === false) {
            return '';
        }

        return '<' . $name . static::renderTagAttributes($options) . '>';
    }

    /**
     * Generates an end tag.
     *
     * @param string|bool|null $name the tag name. If $name is `null` or `false`, the corresponding content will be
     * rendered without any tag.
     * @return string the generated end tag
     * @see beginTag()
     * @see tag()
     */
    public static function endTag($name): string
    {
        if ($name === null || $name === false) {
            return '';
        }

        return "</$name>";
    }

    /**
     * Generates a style tag.
     *
     * @param string $content the style content
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered. See {@see renderTagAttributes()} for details on how attributes are being
     * rendered.
     * @return string the generated style tag
     */
    public static function style(string $content, array $options = []): string
    {
        return static::tag('style', $content, $options);
    }

    /**
     * Generates a script tag.
     *
     * @param string $content the script content
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered. See {@see renderTagAttributes()} for details on how attributes are being
     * rendered.
     * @return string the generated script tag
     */
    public static function script(string $content, array $options = []): string
    {
        return static::tag('script', $content, $options);
    }

    /**
     * Generates a link tag that refers to an external CSS file.
     *
     * @param string $url the URL of the external CSS file. This parameter will be processed.
     * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
     *
     * - condition: specifies the conditional comments for IE, e.g., `lt IE 9`. When this is specified, the generated
     *   `link` tag will be enclosed within the conditional comments. This is mainly useful for supporting old versions
     *   of IE browsers.
     * - noscript: if set to true, `link` tag will be wrapped into `<noscript>` tags.
     *
     * The rest of the options will be rendered as the attributes of the resulting link tag. The values will be
     * HTML-encoded using {@see encode()}. If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated link tag.
     */
    public static function cssFile(string $url, array $options = []): string
    {
        if (!isset($options['rel'])) {
            $options['rel'] = 'stylesheet';
        }
        $options['href'] = $url;

        if (isset($options['condition'])) {
            $condition = $options['condition'];
            unset($options['condition']);
            return self::wrapIntoCondition(static::tag('link', '', $options), $condition);
        } elseif (isset($options['noscript']) && $options['noscript'] === true) {
            unset($options['noscript']);
            return '<noscript>' . static::tag('link', '', $options) . '</noscript>';
        }

        return static::tag('link', '', $options);
    }

    /**
     * Generates a script tag that refers to an external JavaScript file.
     *
     * @param string $url the URL of the external JavaScript file. This parameter will be processed.
     * @param array $options the tag options in terms of name-value pairs. The following option is specially handled:
     *
     * - condition: specifies the conditional comments for IE, e.g., `lt IE 9`. When this is specified, the generated
     *   `script` tag will be enclosed within the conditional comments. This is mainly useful for supporting old
     *   versions of IE browsers.
     *
     * The rest of the options will be rendered as the attributes of the resulting script tag. The values will be
     * HTML-encoded using {@see encode()}. If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated script tag.
     */
    public static function jsFile(string $url, array $options = []): string
    {
        $options['src'] = $url;
        if (isset($options['condition'])) {
            $condition = $options['condition'];
            unset($options['condition']);
            return self::wrapIntoCondition(static::tag('script', '', $options), $condition);
        }

        return static::tag('script', '', $options);
    }

    /**
     * Wraps given content into conditional comments for IE, e.g., `lt IE 9`.
     *
     * @param string $content raw HTML content.
     * @param string $condition condition string.
     * @return string generated HTML.
     */
    private static function wrapIntoCondition(string $content, string $condition): string
    {
        if (strpos($condition, '!IE') !== false) {
            return "<!--[if $condition]><!-->\n" . $content . "\n<!--<![endif]-->";
        }

        return "<!--[if $condition]>\n" . $content . "\n<![endif]-->";
    }

    /**
     * Generates a hyperlink tag.
     *
     * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image
     * tag. If this is coming from end users, you should consider {@see encode()} it to prevent XSS attacks.
     * @param array|string|null $url the URL for the hyperlink tag. This parameter will be processed and will be used
     * for the "href" attribute of the tag. If this parameter is null, the "href" attribute will not
     * be generated.
     *
     * If you want to use an absolute url you can call yourself, before passing the URL to this method, like this:
     *
     * ```php
     * Html::a('link text', $url, true))
     * ```
     *
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}.
     * If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated hyperlink.
     */
    public static function a(string $text, $url = null, array $options = []): string
    {
        if ($url !== null) {
            $options['href'] = $url;
        }

        return static::tag('a', $text, $options);
    }

    /**
     * Generates a mailto hyperlink.
     *
     * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image
     * tag. If this is coming from end users, you should consider {@see encode()} it to prevent XSS attacks.
     * @param string $email email address. If this is null, the first parameter (link body) will be treated
     * as the email address and used.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated mailto link
     */
    public static function mailto(string $text, string $email = null, array $options = []): string
    {
        $options['href'] = 'mailto:' . ($email ?? $text);
        return static::tag('a', $text, $options);
    }

    /**
     * Generates an image tag.
     *
     * @param array|string $src the image URL. This parameter will be processed.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}.
     * If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * It is possible to pass the `srcset` option as an array which keys are descriptors and
     * values are URLs. All URLs will be processed.
     * @return string the generated image tag.
     */
    public static function img(string $src, array $options = []): string
    {
        $options['src'] = $src;

        if (isset($options['srcset']) && is_array($options['srcset'])) {
            $srcset = [];
            foreach ($options['srcset'] as $descriptor => $url) {
                $srcset[] = $url . ' ' . $descriptor;
            }
            $options['srcset'] = implode(',', $srcset);
        }

        if (!isset($options['alt'])) {
            $options['alt'] = '';
        }

        return static::tag('img', '', $options);
    }

    /**
     * Generates a label tag.
     *
     * @param string $content label text. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an
     * image tag. If this is is coming from end users, you should {@see encode()} it to prevent XSS attacks.
     * @param string $for the ID of the HTML element that this label is associated with.
     * If this is null, the "for" attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the
     * corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated label tag.
     */
    public static function label(string $content, string $for = null, array $options = []): string
    {
        $options['for'] = $for;
        return static::tag('label', $content, $options);
    }

    /**
     * Generates a button tag.
     *
     * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded. Therefore you
     * can pass in HTML code such as an image tag. If this is is coming from end users, you should consider
     * {@see encode()} it to prevent XSS attacks.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the
     * corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated button tag.
     */
    public static function button(string $content = 'Button', array $options = []): string
    {
        if (!isset($options['type'])) {
            $options['type'] = 'button';
        }

        return static::tag('button', $content, $options);
    }

    /**
     * Generates a submit button tag.
     *
     * Be careful when naming form elements such as submit buttons. According to the [jQuery documentation]
     * (https://api.jquery.com/submit/) there are some reserved names that can cause conflicts, e.g. `submit`, `length`
     * or `method`.
     *
     * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded. Therefore you
     * can pass in HTML code such as an image tag. If this is is coming from end users, you should consider
     * {@see encode()} it to prevent XSS attacks.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated submit button tag
     */
    public static function submitButton(string $content = 'Submit', array $options = []): string
    {
        $options['type'] = 'submit';
        return static::button($content, $options);
    }

    /**
     * Generates a reset button tag.
     *
     * @param string $content the content enclosed within the button tag. It will NOT be HTML-encoded. Therefore you
     * can pass in HTML code such as an image tag. If this is is coming from end users, you should consider
     * {@see encode()} it to prevent XSS attacks.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated reset button tag
     */
    public static function resetButton(string $content = 'Reset', array $options = []): string
    {
        $options['type'] = 'reset';
        return static::button($content, $options);
    }

    /**
     * Generates an input type of the given type.
     *
     * @param string $type the type attribute.
     * @param string $name the name attribute. If it is null, the name attribute will not be generated.
     * @param string $value the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}.
     * If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated input tag
     */
    public static function input(string $type, string $name = null, string $value = null, array $options = []): string
    {
        if (!isset($options['type'])) {
            $options['type'] = $type;
        }
        $options['name'] = $name;
        $options['value'] = $value === null ? null : (string) $value;
        return static::tag('input', '', $options);
    }

    /**
     * Generates an input button.
     *
     * @param string $label the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated button tag
     */
    public static function buttonInput(string $label = 'Button', array $options = []): string
    {
        $options['type'] = 'button';
        $options['value'] = $label;
        return static::tag('input', '', $options);
    }

    /**
     * Generates a submit input button.
     *
     * Be careful when naming form elements such as submit buttons. According to the [jQuery documentation]
     * https://api.jquery.com/submit/) there are some reserved names that can cause conflicts, e.g. `submit`, `length`
     * or `method`.
     *
     * @param string $label the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated button tag
     */
    public static function submitInput($label = 'Submit', $options = []): string
    {
        $options['type'] = 'submit';
        $options['value'] = $label;
        return static::tag('input', '', $options);
    }

    /**
     * Generates a reset input button.
     *
     * @param string $label the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the attributes of the button tag. The values will be HTML-encoded using {@see encode()}.
     * Attributes whose value is null will be ignored and not put in the tag returned.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated button tag
     */
    public static function resetInput(string $label = 'Reset', array $options = []): string
    {
        $options['type'] = 'reset';
        $options['value'] = $label;
        return static::tag('input', '', $options);
    }

    /**
     * Generates a text input field.
     *
     * @param string $name the name attribute.
     * @param string $value the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null,
     * the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated text input tag
     */
    public static function textInput(string $name, string $value = null, array $options = []): string
    {
        return static::input('text', $name, $value, $options);
    }

    /**
     * Generates a hidden input field.
     *
     * @param string $name the name attribute.
     * @param string $value the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated hidden input tag
     */
    public static function hiddenInput(string $name, string $value = null, array $options = []): string
    {
        return static::input('hidden', $name, $value, $options);
    }

    /**
     * Generates a password input field.
     *
     * @param string $name the name attribute.
     * @param string $value the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated password input tag
     */
    public static function passwordInput(string $name, string $value = null, array $options = []): string
    {
        return static::input('password', $name, $value, $options);
    }

    /**
     * Generates a file input field.
     *
     * To use a file input field, you should set the enclosing form's "enctype" attribute to be "multipart/form-data".
     * After the form is submitted, the uploaded file information can be obtained via $_FILES[$name]
     * (see PHP documentation).
     * @param string $name the name attribute.
     * @param string $value the value attribute. If it is null, the value attribute will not be generated.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null,
     * the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated file input tag
     */
    public static function fileInput(string $name, string $value = null, array $options = []): string
    {
        return static::input('file', $name, $value, $options);
    }

    /**
     * Generates a text area input.
     *
     * @param string $name the input name
     * @param string|null $value the input value. Note that it will be encoded using {@see encode()}.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as the attributes of
     * the resulting tag. The values will be HTML-encoded using {@see encode()}. If a value is null, the corresponding
     * attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     * @return string the generated text area tag
     */
    public static function textarea(string $name, ?string $value = '', array $options = []): string
    {
        $options['name'] = $name;
        return static::tag('textarea', static::encode($value), $options);
    }

    /**
     * Generates a radio button input.
     *
     * @param string $name the name attribute.
     * @param bool $checked whether the radio button should be checked.
     * @param array $options the tag options in terms of name-value pairs.
     * See {@see booleanInput()} for details about accepted attributes.
     *
     * @return string the generated radio button tag
     */
    public static function radio(string $name, bool $checked = false, array $options = []): string
    {
        return static::booleanInput('radio', $name, $checked, $options);
    }

    /**
     * Generates a checkbox input.
     *
     * @param string $name the name attribute.
     * @param bool $checked whether the checkbox should be checked.
     * @param array $options the tag options in terms of name-value pairs.
     * See {@see booleanInput()} for details about accepted attributes.
     *
     * @return string the generated checkbox tag
     */
    public static function checkbox(string $name, bool $checked = false, array $options = []): string
    {
        return static::booleanInput('checkbox', $name, $checked, $options);
    }

    /**
     * Generates a boolean input.
     *
     * @param string $type the input type. This can be either `radio` or `checkbox`.
     * @param string $name the name attribute.
     * @param bool $checked whether the checkbox should be checked.
     * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
     *
     * - uncheck: string, the value associated with the uncheck state of the checkbox. When this attribute is present,
     *   a hidden input will be generated so that if the checkbox is not checked and is submitted, the value of this
     *   attribute will still be submitted to the server via the hidden input.
     * - label: string, a label displayed next to the checkbox. It will NOT be HTML-encoded. Therefore you can pass in
     *   HTML code such as an image tag. If this is is coming from end users, you should {@see encode()} it to prevent
     *   XSS attacks.
     *   When this option is specified, the checkbox will be enclosed by a label tag.
     * - labelOptions: array, the HTML attributes for the label tag. Do not set this option unless you set the "label"
     *   option.
     *
     * The rest of the options will be rendered as the attributes of the resulting checkbox tag. The values will be
     * HTML-encoded using {@see encode()}. If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated checkbox tag
     */
    protected static function booleanInput(string $type, string $name, bool $checked = false, array $options = []): string
    {
        $options['checked'] = (bool) $checked;
        $value = array_key_exists('value', $options) ? $options['value'] : '1';
        if (isset($options['uncheck'])) {
            // add a hidden field so that if the checkbox is not selected, it still submits a value
            $hiddenOptions = [];
            if (isset($options['form'])) {
                $hiddenOptions['form'] = $options['form'];
            }
            // make sure disabled input is not sending any value
            if (!empty($options['disabled'])) {
                $hiddenOptions['disabled'] = $options['disabled'];
            }
            $hidden = static::hiddenInput($name, $options['uncheck'], $hiddenOptions);
            unset($options['uncheck']);
        } else {
            $hidden = '';
        }
        if (isset($options['label'])) {
            $label = $options['label'];
            $labelOptions = $options['labelOptions'] ?? [];
            unset($options['label'], $options['labelOptions']);
            $content = static::label(static::input($type, $name, $value, $options) . ' ' . $label, null, $labelOptions);
            return $hidden . $content;
        }

        return $hidden . static::input($type, $name, $value, $options);
    }

    /**
     * Generates a drop-down list.
     *
     * @param string $name the input name
     * @param string|array|null $selection the selected value(s). String for single or array for multiple selection(s).
     * @param array $items the option data items. The array keys are option values, and the array values are the
     * corresponding option labels. The array can also be nested (i.e. some array values are arrays too). For each
     * sub-array, an option group will be generated whose label is the key associated with the sub-array. If you have a
     * list of data models, you may convert them into the format described above using
     * {@see \Yiisoft\Arrays\ArrayHelper::map()}.
     *
     * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in the
     * labels will also be HTML-encoded.
     * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
     *
     * - prompt: string, a prompt text to be displayed as the first option. You can use an array to override the value
     *   and to set other tag attributes:
     *
     *   ```php
     *   ['text' => 'Please select', 'options' => ['value' => 'none', 'class' => 'prompt', 'label' => 'Select']],
     *   ```
     *
     * - options: array, the attributes for the select option tags. The array keys must be valid option values, and the
     *   array values are the extra attributes for the corresponding option tags. For example,
     *
     *   ```php
     *   [
     *       'value1' => ['disabled' => true],
     *       'value2' => ['label' => 'value 2'],
     *   ];
     *   ```
     *
     * - groups: array, the attributes for the optgroup tags. The structure of this is similar to that of 'options',
     *   except that the array keys represent the optgroup labels specified in $items.
     * - encodeSpaces: bool, whether to encode spaces in option prompt and option value with `&nbsp;` character.
     *   Defaults to false.
     * - encode: bool, whether to encode option prompt and option value characters. Defaults to `true`.
     *
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
     * using {@see encode()}. If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated drop-down list tag
     */
    public static function dropDownList(string $name, $selection = null, array $items = [], array $options = []): string
    {
        if (!empty($options['multiple'])) {
            return static::listBox($name, $selection, $items, $options);
        }
        $options['name'] = $name;
        unset($options['unselect']);
        $selectOptions = static::renderSelectOptions($selection, $items, $options);
        return static::tag('select', "\n" . $selectOptions . "\n", $options);
    }

    /**
     * Generates a list box.
     *
     * @param string $name the input name
     * @param string|array|null $selection the selected value(s). String for single or array for multiple selection(s).
     * @param array $items the option data items. The array keys are option values, and the array values are the
     * corresponding option labels. The array can also be nested (i.e. some array values are arrays too). For each
     * sub-array, an option group will be generated whose label is the key associated with the sub-array. If you have a
     * list of data models, you may convert them into the format described above using
     * {@see \Yiisoft\Arrays\ArrayHelper::map()}.
     *
     * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in the
     * labels will also be HTML-encoded.
     * @param array $options the tag options in terms of name-value pairs. The following options are specially handled:
     *
     * - prompt: string, a prompt text to be displayed as the first option. You can use an array to override the value
     *   and to set other tag attributes:
     *
     *   ```php
     *   ['text' => 'Please select', 'options' => ['value' => 'none', 'class' => 'prompt', 'label' => 'Select']],
     *   ```
     *
     * - options: array, the attributes for the select option tags. The array keys must be valid option values, and the
     *   array values are the extra attributes for the corresponding option tags. For example,
     *
     *   ```php
     *   [
     *       'value1' => ['disabled' => true],
     *       'value2' => ['label' => 'value 2'],
     *   ];
     *   ```
     *
     * - groups: array, the attributes for the optgroup tags. The structure of this is similar to that of 'options',
     *   except that the array keys represent the optgroup labels specified in $items.
     * - unselect: string, the value that will be submitted when no option is selected.
     *   When this attribute is set, a hidden field will be generated so that if no option is selected in multiple
     *   mode, we can still obtain the posted unselect value.
     * - encodeSpaces: bool, whether to encode spaces in option prompt and option value with `&nbsp;` character.
     *   Defaults to false.
     * - encode: bool, whether to encode option prompt and option value characters. Defaults to `true`.
     *
     * The rest of the options will be rendered as the attributes of the resulting tag. The values will be HTML-encoded
     * using {@see encode()}. If a value is null, the corresponding attribute will not be rendered.
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated list box tag.
     */
    public static function listBox(string $name, $selection = null, array $items = [], array $options = []): string
    {
        if (!array_key_exists('size', $options)) {
            $options['size'] = 4;
        }
        if (!empty($options['multiple']) && !empty($name) && substr_compare($name, '[]', -2, 2)) {
            $name .= '[]';
        }
        $options['name'] = $name;
        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            if (!empty($name) && substr_compare($name, '[]', -2, 2) === 0) {
                $name = substr($name, 0, -2);
            }
            $hiddenOptions = [];
            // make sure disabled input is not sending any value
            if (!empty($options['disabled'])) {
                $hiddenOptions['disabled'] = $options['disabled'];
            }
            $hidden = static::hiddenInput($name, $options['unselect'], $hiddenOptions);
            unset($options['unselect']);
        } else {
            $hidden = '';
        }
        $selectOptions = static::renderSelectOptions($selection, $items, $options);
        return $hidden . static::tag('select', "\n" . $selectOptions . "\n", $options);
    }

    /**
     * Generates a list of checkboxes.
     *
     * A checkbox list allows multiple selection, like {@see listBox()}. As a result, the corresponding submitted value
     * is an array.
     * @param string $name the name attribute of each checkbox.
     * @param string|array|null $selection the selected value(s). String for single or array for multiple selection(s).
     * @param array $items the data item used to generate the checkboxes. The array keys are the checkbox values, while
     * the array values are the corresponding labels.
     * @param array $options options (name => config) for the checkbox list container tag. The following options are
     * specially handled:
     *
     * - tag: string|false, the tag name of the container element. False to render checkbox without container.
     *   See also {@see tag()}.
     * - unselect: string, the value that should be submitted when none of the checkboxes is selected. By setting this
     *   option, a hidden input will be generated.
     * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true. This option is ignored if `item`
     *   option is set.
     * - separator: string, the HTML code that separates items.
     * - itemOptions: array, the options for generating the checkbox tag using {@see checkbox()}.
     * - item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     *
     *   ```php
     *   function ($index, $label, $name, $checked, $value)
     *   ```
     *
     *   where $index is the zero-based index of the checkbox in the whole list; $label is the label for the checkbox;
     *   and $name, $value and $checked represent the name, value and the checked status of the checkbox input,
     *   respectively.
     *
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated checkbox list
     */
    public static function checkboxList(string $name, $selection = null, array $items = [], array $options = []): string
    {
        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }
        if (ArrayHelper::isTraversable($selection)) {
            $selection = array_map('strval', (array)$selection);
        }

        $formatter = ArrayHelper::remove($options, 'item');
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);
        $encode = ArrayHelper::remove($options, 'encode', true);
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        $lines = [];
        $index = 0;
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                ((!ArrayHelper::isTraversable($selection) && !strcmp($value, $selection))
                    || (ArrayHelper::isTraversable($selection) && ArrayHelper::isIn((string)$value, $selection)));
            if ($formatter !== null) {
                $lines[] = $formatter($index, $label, $name, $checked, $value);
            } else {
                $lines[] = static::checkbox($name, $checked, array_merge($itemOptions, [
                    'value' => $value,
                    'label' => $encode ? static::encode($label) : $label,
                ]));
            }
            $index++;
        }

        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            $name2 = substr($name, -2) === '[]' ? substr($name, 0, -2) : $name;
            $hiddenOptions = [];
            // make sure disabled input is not sending any value
            if (!empty($options['disabled'])) {
                $hiddenOptions['disabled'] = $options['disabled'];
            }
            $hidden = static::hiddenInput($name2, $options['unselect'], $hiddenOptions);
            unset($options['unselect'], $options['disabled']);
        } else {
            $hidden = '';
        }

        $visibleContent = implode($separator, $lines);

        if ($tag === false) {
            return $hidden . $visibleContent;
        }

        return $hidden . static::tag($tag, $visibleContent, $options);
    }

    /**
     * Generates a list of radio buttons.
     *
     * A radio button list is like a checkbox list, except that it only allows single selection.
     * @param string $name the name attribute of each radio button.
     * @param string|array|null $selection the selected value(s). String for single or array for multiple selection(s).
     * @param array $items the data item used to generate the radio buttons. The array keys are the radio button
     * values, while the array values are the corresponding labels.
     * @param array $options options (name => config) for the radio button list container tag. The following options
     * are specially handled:
     *
     * - tag: string|false, the tag name of the container element. False to render radio buttons without container.
     *   See also {@see tag()}.
     * - unselect: string, the value that should be submitted when none of the radio buttons is selected. By setting
     *   this option, a hidden input will be generated.
     * - encode: boolean, whether to HTML-encode the checkbox labels. Defaults to true. This option is ignored if `item`
     *   option is set.
     * - separator: string, the HTML code that separates items.
     * - itemOptions: array, the options for generating the radio button tag using {@see radio()}.
     * - item: callable, a callback that can be used to customize the generation of the HTML code corresponding to a
     *   single item in $items. The signature of this callback must be:
     *
     *   ```php
     *   function ($index, $label, $name, $checked, $value)
     *   ```
     *
     *   where $index is the zero-based index of the radio button in the whole list; $label is the label for the radio
     *   button; and $name, $value and $checked represent the name, value and the checked status of the radio button
     *   input, respectively.
     *
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated radio button list
     */
    public static function radioList(string $name, $selection = null, array $items = [], array $options = []): string
    {
        if (ArrayHelper::isTraversable($selection)) {
            $selection = array_map('strval', (array)$selection);
        }

        $formatter = ArrayHelper::remove($options, 'item');
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);
        $encode = ArrayHelper::remove($options, 'encode', true);
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        $hidden = '';
        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            $hiddenOptions = [];
            // make sure disabled input is not sending any value
            if (!empty($options['disabled'])) {
                $hiddenOptions['disabled'] = $options['disabled'];
            }
            $hidden = static::hiddenInput($name, $options['unselect'], $hiddenOptions);
            unset($options['unselect'], $options['disabled']);
        }

        $lines = [];
        $index = 0;
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                ((!ArrayHelper::isTraversable($selection) && !strcmp($value, $selection))
                    || (ArrayHelper::isTraversable($selection) && ArrayHelper::isIn((string)$value, $selection)));
            if ($formatter !== null) {
                $lines[] = $formatter($index, $label, $name, $checked, $value);
            } else {
                $lines[] = static::radio($name, $checked, array_merge($itemOptions, [
                    'value' => $value,
                    'label' => $encode ? static::encode($label) : $label,
                ]));
            }
            $index++;
        }
        $visibleContent = implode($separator, $lines);

        if ($tag === false) {
            return $hidden . $visibleContent;
        }

        return $hidden . static::tag($tag, $visibleContent, $options);
    }

    /**
     * Generates an unordered list.
     *
     * @param array|\Traversable $items the items for generating the list. Each item generates a single list item. Note
     * that items will be automatically HTML encoded if `$options['encode']` is not set or true.
     * @param array $options options (name => config) for the radio button list. The following options are supported:
     *
     * - encode: boolean, whether to HTML-encode the items. Defaults to true. This option is ignored if the `item`
     *   option is specified.
     * - separator: string, the HTML code that separates items. Defaults to a simple newline (`"\n"`). This option is
     *   available.
     * - itemOptions: array, the HTML attributes for the `li` tags. This option is ignored if the `item` option is
     *   specified.
     * - item: callable, a callback that is used to generate each individual list item. The signature of this callback
     *   must be:
     *
     *   ```php
     *   function ($item, $index)
     *   ```
     *
     *   where $index is the array key corresponding to `$item` in `$items`. The callback should return the whole list
     *   item tag.
     *
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated unordered list. An empty list tag will be returned if `$items` is empty.
     */
    public static function ul(array $items, array $options = []): string
    {
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
        $encode = ArrayHelper::remove($options, 'encode', true);
        $formatter = ArrayHelper::remove($options, 'item');
        $separator = ArrayHelper::remove($options, 'separator', "\n");
        $itemOptions = ArrayHelper::remove($options, 'itemOptions', []);

        if (empty($items)) {
            return static::tag($tag, '', $options);
        }

        $results = [];
        foreach ($items as $index => $item) {
            if ($formatter !== null) {
                $results[] = $formatter($item, $index);
            } else {
                $results[] = static::tag('li', $encode ? static::encode($item) : $item, $itemOptions);
            }
        }

        return static::tag(
            $tag,
            $separator . implode($separator, $results) . $separator,
            $options
        );
    }

    /**
     * Generates an ordered list.
     *
     * @param array|\Traversable $items the items for generating the list. Each item generates a single list item. Note
     * that items will be automatically HTML encoded if `$options['encode']` is not set or true.
     * @param array $options options (name => config) for the radio button list. The following options are supported:
     *
     * - encode: boolean, whether to HTML-encode the items. Defaults to true. This option is ignored if the `item`
     *   option is specified.
     * - itemOptions: array, the HTML attributes for the `li` tags. This option is ignored if the `item` option is
     *   specified.
     * - item: callable, a callback that is used to generate each individual list item. The signature of this callback
     *   must be:
     *
     *   ```php
     *   function ($item, $index)
     *   ```
     *
     *   where $index is the array key corresponding to `$item` in `$items`. The callback should return the whole list
     *   item tag.
     *
     * See {@see renderTagAttributes()} for details on how attributes are being rendered.
     *
     * @return string the generated ordered list. An empty string is returned if `$items` is empty.
     */
    public static function ol(array $items, array $options = []): string
    {
        $options['tag'] = 'ol';
        return static::ul($items, $options);
    }

    /**
     * Renders the option tags that can be used by {@see dropDownList()} and {@see listBox()}.
     *
     * @param string|array|null $selection the selected value(s). String for single or array for multiple selection(s).
     * @param array $items the option data items. The array keys are option values, and the array values are the
     * corresponding option labels. The array can also be nested (i.e. some array values are arrays too). For each
     * sub-array, an option group will be generated whose label is the key associated with the sub-array. If you have a
     * list of data models, you may convert them into the format described above using
     * {@see \Yiisoft\Arrays\ArrayHelper::map()}.
     *
     * Note, the values and labels will be automatically HTML-encoded by this method, and the blank spaces in the
     * labels will also be HTML-encoded.
     * @param array $tagOptions the $options parameter that is passed to the {@see dropDownList()} or {@see listBox()}
     * call. This method will take out these elements, if any: "prompt", "options" and "groups". See more details in
     * {@see dropDownList()} for the explanation of these elements.
     *
     * @return string the generated list options
     */
    public static function renderSelectOptions($selection, array $items, array &$tagOptions = []): string
    {
        if (ArrayHelper::isTraversable($selection)) {
            $selection = array_map('strval', (array)$selection);
        }

        $lines = [];
        $encodeSpaces = ArrayHelper::remove($tagOptions, 'encodeSpaces', false);
        $encode = ArrayHelper::remove($tagOptions, 'encode', true);
        if (isset($tagOptions['prompt'])) {
            $promptOptions = ['value' => ''];
            if (is_string($tagOptions['prompt'])) {
                $promptText = $tagOptions['prompt'];
            } else {
                $promptText = $tagOptions['prompt']['text'];
                $promptOptions = array_merge($promptOptions, $tagOptions['prompt']['options']);
            }
            $promptText = $encode ? static::encode($promptText) : $promptText;
            if ($encodeSpaces) {
                $promptText = str_replace(' ', '&nbsp;', $promptText);
            }
            $lines[] = static::tag('option', $promptText, $promptOptions);
        }

        $options = $tagOptions['options'] ?? [];
        $groups = $tagOptions['groups'] ?? [];
        unset($tagOptions['prompt'], $tagOptions['options'], $tagOptions['groups']);
        $options['encodeSpaces'] = ArrayHelper::getValue($options, 'encodeSpaces', $encodeSpaces);
        $options['encode'] = ArrayHelper::getValue($options, 'encode', $encode);

        foreach ($items as $key => $value) {
            if (is_array($value)) {
                $groupAttrs = $groups[$key] ?? [];
                if (!isset($groupAttrs['label'])) {
                    $groupAttrs['label'] = $key;
                }
                $attrs = [
                    'options' => $options,
                    'groups' => $groups,
                    'encodeSpaces' => $encodeSpaces,
                    'encode' => $encode
                ];
                $content = static::renderSelectOptions($selection, $value, $attrs);
                $lines[] = static::tag('optgroup', "\n" . $content . "\n", $groupAttrs);
            } else {
                $attrs = $options[$key] ?? [];
                $attrs['value'] = (string) $key;
                if (!array_key_exists('selected', $attrs)) {
                    $attrs['selected'] = $selection !== null &&
                        ((!ArrayHelper::isTraversable($selection) && !strcmp($key, $selection))
                        || (ArrayHelper::isTraversable($selection) && ArrayHelper::isIn((string)$key, $selection)));
                }
                $text = $encode ? static::encode($value) : $value;
                if ($encodeSpaces) {
                    $text = str_replace(' ', '&nbsp;', $text);
                }
                $lines[] = static::tag('option', $text, $attrs);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Renders the HTML tag attributes.
     *
     * Attributes whose values are of boolean type will be treated as
     * [boolean attributes](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
     *
     * Attributes whose values are null will not be rendered. The values of attributes will be HTML-encoded using
     * {@see encode()}.
     *
     * The "data" attribute is specially handled when it is receiving an array value. In this case, the array will be
     * "expanded" and a list data attributes will be rendered. For example, if `'data' => ['id' => 1, 'name' => 'yii']`
     * then this will be rendered:
     *
     * `data-id="1" data-name="yii"`.
     *
     * Additionally `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` will be rendered as:
     * `data-params='{"id":1,"name":"yii"}' data-status="ok"`.
     *
     * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using
     * {@see encode()}.
     * @return string the rendering result. If the attributes are not empty, they will be rendered into a string
     * with a leading white space (so that it can be directly appended to the tag name in a tag. If there is no
     * attribute, an empty string will be returned.
     * @see addCssClass()
     */
    public static function renderTagAttributes(array $attributes): string
    {
        if (count($attributes) > 1) {
            $sorted = [];
            foreach (static::$attributeOrder as $name) {
                if (isset($attributes[$name])) {
                    $sorted[$name] = $attributes[$name];
                }
            }
            $attributes = array_merge($sorted, $attributes);
        }

        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, static::$dataAttributes, true)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . Json::htmlEncode($v) . "'";
                        } else {
                            $html .= " $name-$n=\"" . static::encode($v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(implode(' ', $value)) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(static::cssStyleFromArray($value)) . '"';
                } else {
                    $html .= " $name='" . Json::htmlEncode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" . static::encode($value) . '"';
            }
        }

        return $html;
    }

    /**
     * Adds a CSS class (or several classes) to the specified options.
     *
     * If the CSS class is already in the options, it will not be added again. If class specification at given options
     * is an array, and some class placed there with the named (string) key, overriding of such key will have no
     * effect. For example:
     *
     * ```php
     * $options = ['class' => ['persistent' => 'initial']];
     * Html::addCssClass($options, ['persistent' => 'override']);
     * var_dump($options['class']); // outputs: array('persistent' => 'initial');
     * ```
     *
     * @param array $options the options to be modified.
     * @param string|array $class the CSS class(es) to be added
     * @see mergeCssClasses()
     * @see removeCssClass()
     */
    public static function addCssClass(array &$options, $class): void
    {
        if (isset($options['class'])) {
            if (is_array($options['class'])) {
                $options['class'] = self::mergeCssClasses($options['class'], (array) $class);
            } else {
                $classes = preg_split('/\s+/', $options['class'], -1, PREG_SPLIT_NO_EMPTY);
                $options['class'] = implode(' ', self::mergeCssClasses($classes, (array) $class));
            }
        } else {
            $options['class'] = $class;
        }
    }

    /**
     * Merges already existing CSS classes with new one.
     *
     * This method provides the priority for named existing classes over additional.
     * @param array $existingClasses already existing CSS classes.
     * @param array $additionalClasses CSS classes to be added.
     * @return array merge result.
     * @see addCssClass()
     */
    private static function mergeCssClasses(array $existingClasses, array $additionalClasses): array
    {
        foreach ($additionalClasses as $key => $class) {
            if (is_int($key) && !in_array($class, $existingClasses)) {
                $existingClasses[] = $class;
            } elseif (!isset($existingClasses[$key])) {
                $existingClasses[$key] = $class;
            }
        }

        return array_unique($existingClasses);
    }

    /**
     * Removes a CSS class from the specified options.
     *
     * @param array $options the options to be modified.
     * @param string|array $class the CSS class(es) to be removed
     * @see addCssClass()
     */
    public static function removeCssClass(array &$options, $class): void
    {
        if (isset($options['class'])) {
            if (is_array($options['class'])) {
                $classes = array_diff($options['class'], (array) $class);
                if (empty($classes)) {
                    unset($options['class']);
                } else {
                    $options['class'] = $classes;
                }
            } else {
                $classes = preg_split('/\s+/', $options['class'], -1, PREG_SPLIT_NO_EMPTY);
                $classes = array_diff($classes, (array) $class);
                if (empty($classes)) {
                    unset($options['class']);
                } else {
                    $options['class'] = implode(' ', $classes);
                }
            }
        }
    }

    /**
     * Adds the specified CSS style to the HTML options.
     *
     * If the options already contain a `style` element, the new style will be merged
     * with the existing one. If a CSS property exists in both the new and the old styles,
     * the old one may be overwritten if `$overwrite` is true.
     *
     * For example,
     *
     * ```php
     * Html::addCssStyle($options, 'width: 100px; height: 200px');
     * ```
     *
     * @param array $options the HTML options to be modified.
     * @param string|array $style the new style string (e.g. `'width: 100px; height: 200px'`) or array
     * (e.g. `['width' => '100px', 'height' => '200px']`).
     * @param bool $overwrite whether to overwrite existing CSS properties if the new style
     * contain them too.
     * @see removeCssStyle()
     * @see cssStyleFromArray()
     * @see cssStyleToArray()
     */
    public static function addCssStyle(array &$options, $style, bool $overwrite = true): void
    {
        if (!empty($options['style'])) {
            $oldStyle = is_array($options['style']) ? $options['style'] : static::cssStyleToArray($options['style']);
            $newStyle = is_array($style) ? $style : static::cssStyleToArray($style);
            if (!$overwrite) {
                foreach ($newStyle as $property => $value) {
                    if (isset($oldStyle[$property])) {
                        unset($newStyle[$property]);
                    }
                }
            }
            $style = array_merge($oldStyle, $newStyle);
        }
        $options['style'] = is_array($style) ? static::cssStyleFromArray($style) : $style;
    }

    /**
     * Removes the specified CSS style from the HTML options.
     *
     * For example,
     *
     * ```php
     * Html::removeCssStyle($options, ['width', 'height']);
     * ```
     *
     * @param array $options the HTML options to be modified.
     * @param string|array $properties the CSS properties to be removed. You may use a string if you are removing a
     * single property.
     * @see addCssStyle()
     */
    public static function removeCssStyle(array &$options, $properties): void
    {
        if (!empty($options['style'])) {
            $style = is_array($options['style']) ? $options['style'] : static::cssStyleToArray($options['style']);
            foreach ((array) $properties as $property) {
                unset($style[$property]);
            }
            $options['style'] = static::cssStyleFromArray($style);
        }
    }

    /**
     * Converts a CSS style array into a string representation.
     *
     * For example,
     *
     * ```php
     * print_r(Html::cssStyleFromArray(['width' => '100px', 'height' => '200px']));
     * // will display: 'width: 100px; height: 200px;'
     * ```
     *
     * @param array $style the CSS style array. The array keys are the CSS property names, and the array values are the
     * corresponding CSS property values.
     * @return string|null the CSS style string. If the CSS style is empty, a null will be returned.
     */
    public static function cssStyleFromArray(array $style):?string
    {
        $result = '';
        foreach ($style as $name => $value) {
            $result .= "$name: $value; ";
        }

        // return null if empty to avoid rendering the "style" attribute
        return $result === '' ? null : rtrim($result);
    }

    /**
     * Converts a CSS style string into an array representation.
     *
     * The array keys are the CSS property names, and the array values are the corresponding CSS property values.
     *
     * For example,
     *
     * ```php
     * print_r(Html::cssStyleToArray('width: 100px; height: 200px;'));
     * // will display: ['width' => '100px', 'height' => '200px']
     * ```
     *
     * @param string $style the CSS style string
     * @return array the array representation of the CSS style
     */
    public static function cssStyleToArray(string $style): array
    {
        $result = [];
        foreach (explode(';', $style) as $property) {
            $property = explode(':', $property);
            if (count($property) > 1) {
                $result[trim($property[0])] = trim($property[1]);
            }
        }

        return $result;
    }

    /**
     * Returns the real attribute name from the given attribute expression.
     *
     * An attribute expression is an attribute name prefixed and/or suffixed with array indexes. It is mainly used in
     * tabular data input and/or input of array type. Below are some examples:
     *
     * - `[0]content` is used in tabular data input to represent the "content" attribute for the first model in tabular
     *    input;
     * - `dates[0]` represents the first array element of the "dates" attribute;
     * - `[0]dates[0]` represents the first array element of the "dates" attribute for the first model in tabular
     *    input.
     *
     * If `$attribute` has neither prefix nor suffix, it will be returned back without change.
     * @param string $attribute the attribute name or expression
     * @return string the attribute name without prefix and suffix.
     * @throws \InvalidArgumentException if the attribute name contains non-word characters.
     */
    public static function getAttributeName(string $attribute):?string
    {
        if (preg_match(static::$attributeRegex, $attribute, $matches)) {
            return $matches[2];
        }

        throw new \InvalidArgumentException('Attribute name must contain word characters only.');
    }

    /**
     * Escapes regular expression to use in JavaScript.
     *
     * @param string $regexp the regular expression to be escaped.
     * @return string the escaped result.
     */
    public static function escapeJsRegularExpression(string $regexp): string
    {
        $pattern = preg_replace('/\\\\x{?([0-9a-fA-F]+)}?/', '\u$1', $regexp);
        $deliminator = substr($pattern, 0, 1);
        $pos = strrpos($pattern, $deliminator, 1);
        $flag = substr($pattern, $pos + 1);
        if ($deliminator !== '/') {
            $pattern = '/' . str_replace('/', '\\/', substr($pattern, 1, $pos - 1)) . '/';
        } else {
            $pattern = substr($pattern, 0, $pos + 1);
        }
        if (!empty($flag)) {
            $pattern .= preg_replace('/[^igm]/', '', $flag);
        }

        return $pattern;
    }
}