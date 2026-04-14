<?php
function buildSelectbox($mode, $name, $emptyOption, $options, $selected = "", $attribute = "")
{
    if ($mode === "input") {
        if ($selected == "") {
            $selected = getDefaultValue($name);
        }
        $selecteHtml = sprintf('<select %s name="%s">', $attribute, $name);
        if ($emptyOption !== "") {
            $selecteHtml .= sprintf('<option value="0">%s</option>', $emptyOption);
        }
        $selecteHtml .= buildInputOptions($options, $selected);
        $selecteHtml .= "</select>";
        return $selecteHtml;
    } elseif ($mode === "confirm") {
        return getInputValue($name);
    }
}
function buildCheckList($mode, $name, $checks, $checkeds = [], $attribute = [])
{
    if ($mode === "input") {
        $checkListHtml = "";
        if (empty($checkeds)) {
            $checkeds = getDefaultValues($name);
        }
        foreach ($checks as $check) {
            $checkListHtml .= sprintf(
                '<label for="%s"><input id="%s" name="%s[]" %s %s type="checkbox" value="%s"><span>%s</span></label>',
                $check,
                $check,
                $name,
                in_array($check, $checkeds) ? "checked='true'" : "",
                isset($attribute[$check]) ? $attribute[$check] : "",
                $check,
                $check
            );
        }
        return $checkListHtml;
    } elseif ($mode === "confirm") {
        return getInputValues($name);
    }
}
function buildRadioList($mode, $name, $radios, $selectRadio = "", $attribute = [])
{
    if ($mode === "input") {
        $radioListHtml = "";
        if ($selectRadio==="") {
            $selectRadio = getDefaultValue($name);
        }
        foreach ($radios as $radio) {
            $radioListHtml .= sprintf(
                '<label for="%s"><input id="%s" name="%s" %s %s type="radio" value="%s"><span>%s</span></label>',
                $radio,
                $radio,
                $name,
                ($radio === $selectRadio) ? "checked='true'" : "",
                isset($attribute[$radio]) ? $attribute[$radio] : "",
                $radio,
                $radio
            );
        }
        return $radioListHtml;
    } elseif ($mode === "confirm") {
        return getInputValue($name);
    }
}
function buildInputField($mode, $name, $type = "text", $attribute = "")
{
    if ($mode === "input") {
        $inputHtml = sprintf('<input %s type="%s" name="%s" value="%s">', $attribute, $type, $name, getDefaultValue($name));
        return $inputHtml;
    } elseif ($mode === "confirm") {
        return getInputValue($name);
    }
}
function buildTextarea($mode, $name, $attribute = "")
{
    if ($mode === "input") {
        $inputHtml = sprintf('<textarea %s name="%s">%s</textarea>', $attribute, $name, getDefaultValue($name));
        return $inputHtml;
    } elseif ($mode === "confirm") {
        return getInputValue($name);
    }
}
function buildInputOptions($options, $selected = "")
{
    $optionsHtml = "";
    foreach ($options as $option) {
        $optionsHtml .= sprintf(
            '<option %s value="%s">%s</option>',
            ($selected === $option) ? "selected=true" : "",
            $option,
            $option
        );
    }
    return $optionsHtml;
}
function getDefaultValue($key)
{
    return isset($_SESSION[$key]) ? $_SESSION[$key] : "";
}
function getDefaultValues($key)
{
    return isset($_SESSION[$key]) ? $_SESSION[$key] : [];
}
function getInputValue($key)
{
    return isset($_SESSION[$key]) ? esc($_SESSION[$key]) : "";
}
function esc($value)
{
    $value = str_replace(array("\r\n", "\r", "\n"), '', $value);
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
function getInputValues($key)
{
    return isset($_SESSION[$key]) ? implode("<br>", array_map('esc', $_SESSION[$key])) : "";
}