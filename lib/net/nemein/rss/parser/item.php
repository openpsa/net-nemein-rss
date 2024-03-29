<?php
/**
 * @package net.nemein.rss
 * @author CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @copyright CONTENT CONTROL http://www.contentcontrol-berlin.de/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

use SimplePie\Item;

/**
 * Helper class for custom RSS handling
 *
 * @package net.nemein.rss
 */
class net_nemein_rss_parser_item extends Item
{
    private bool $_id_missing = true;

    private ?string $_local_guid = null;

    /**
     * @inheritdoc
     */
    public function get_title()
    {
        $title = parent::get_title();
        if (empty($title)) {
            $l10n = midcom::get()->i18n->get_l10n('net.nemein.rss');
            $title = $l10n->get('untitled');
            if ($description = $this->get_description()) {
                // Use 20 first characters from the description as title
                $title = mb_substr(strip_tags($this->_decode($description)), 0, 20) . '...';
            } elseif ($date = $this->get_date('U')) {
                // Use publication date as title
                $title = $l10n->get_formatter()->date($date);
            }
        }
        return $title;
    }

    /**
     * @inheritdoc
     */
    public function get_description($description_only = false)
    {
        return $this->_decode(parent::get_description($description_only));
    }

    /**
     * @inheritdoc
     */
    public function get_content($content_only = false)
    {
        return $this->_decode(parent::get_content($content_only));
    }

    /**
     * @inheritdoc
     */
    public function get_link($key = 0, $rel = 'alternate')
    {
        $link = parent::get_link($key, $rel);
        if (   $rel !== 'alternate'
            || $key !== 0) {
            return $link;
        }

        if (empty($link)) {
            if (!$this->_id_missing) {
                $link = $this->get_id();
            } else {
                // No link or GUID defined
                // TODO: Generate a "link" using channel URL
                $link = '';
            }
        }
        return $link;
    }

    /**
     * @inheritdoc
     */
    public function get_id($hash = false, $fn = 'md5')
    {
        $guid = parent::get_id($hash, false);

        if (empty($guid)) {
            $this->_id_missing = true;
            $guid = parent::get_link();
        }

        return $guid;
    }

    public function get_local_guid() : ?string
    {
        return $this->_local_guid;
    }

    public function set_local_guid(string $guid)
    {
        $this->_local_guid = $guid;
    }

    private function _decode($string) : string
    {
        return html_entity_decode((string) $string, ENT_QUOTES, midcom::get()->i18n->get_current_charset());
    }
}
