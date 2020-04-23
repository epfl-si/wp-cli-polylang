<?php
/**
 * Polylang for WP-CLI
 *
 * Helper global functions
 *
 * This is a temporary file, pending integration to Polylang API (api.php).
 * As this API uses global functions starting with pll_, we follow the convention.
 *
 * @package     WP-CLI
 * @subpackage  Polylang
 * @filesource
 */

/**
 * @return bool
 */
function pll_installed_language_list()
{
    global $polylang;

    return isset($polylang) ? $polylang->model->get_languages_list() : false;
}

/**
 * @param $languageCode
 * @return array|null
 */
function pll_get_default_language_information($languageCode)
{
    global $polylang;

    /* Depending Polylang version, 'languages.php' initializes $languages var or returns an array with all of 
     the languages content. This array has languageCode as key so it's easier to find a language */
    $req_res = (require PLL_SETTINGS_INC.'/languages.php' );
    if(is_array($req_res)) $languages = $req_res;

    if(array_key_exists($languageCode, $languages))
    {
        return array(
            'code'      => $languages[$languageCode]['code'],
            'locale'    => $languages[$languageCode]['locale'],
            'name'      => $languages[$languageCode]['name'],
            'rtl'       => $languages[$languageCode]['dir'],
            'flag'      => $languages[$languageCode]['flag']
        );
    }

    return null;
}

/**
 * @param $languageCode
 * @return bool
 */
function pll_is_valid_language_code($languageCode)
{
    return pll_get_default_language_information($languageCode) !== null;
}

/**
 * @param $languageCode
 * @param int $languageOrder
 * @return mixed
 */
function pll_add_language($languageCode, $languageOrder = 0)
{
    global $polylang;

    $info = pll_get_default_language_information($languageCode);

    $args = array(
        'name'        => $info['name'],
        'slug'        => $info['code'],
        'locale'      => $info['locale'],
        'flag'        => $info['flag'],
        'rtl'         => ($info['rtl'] == 'rtl') ? 1 : 0,
        'term_group'  => $languageOrder
    );

    return $polylang->model->add_language($args);
}

/**
 * @param $languageCode
 * @return bool
 */
function pll_del_language($languageCode)
{
    global $polylang;

    $languages = pll_installed_language_list();

    if (!$languages) {
        return false;
    }

    foreach ($languages as $language) {
        if ($language->slug == $languageCode || $language->locale == $languageCode) {
            $polylang->model->delete_language((int)$language->term_id);

            return true;
        }
    }

    return false;
}

/**
 * @param $languageCode
 * @return bool
 */
function pll_is_language_installed($languageCode)
{
    $languages = pll_installed_language_list();

    if (!$languages) {
        return false;
    }

    foreach ($languages as $language) {
        if ($language->slug == $languageCode || $language->locale == $languageCode) {
            return true;
        }
    }

    return false;
}

/**
 * @param $type
 * @param $slug
 * @return mixed
 */
function pll_get_id_by_slug($type, $slug)
{
    switch ($type) {
        case 'post':

            $query = new WP_Query(['pagename' => $slug, 'post_type' => ['post', 'page', 'event']]);
            $posts = $query->get_posts();
            if (!empty($posts)) {
                return $posts[0]->ID;
            }

            break;
        case 'term':

            $term = get_term_by('slug', $slug, 'category');
            if ($term) {
                return (int)$term->term_id;
            }

            break;
        default:
            break;
    }

    return $slug;
}