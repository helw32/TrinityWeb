<?php

namespace common\modules\forum\helpers;

use common\modules\forum\models\Post;
use common\modules\forum\models\User;
use common\modules\forum\Podium;
use DateTime;
use DateTimeZone;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/**
 * Podium Helper
 * Static methods for HTML output and other little things.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Helper
{
    /**
     * Prepares content for categories administration.
     * @param mixed $category
     * @return string
     */
    public static function adminCategoriesPrepareContent($category)
    {
        $actions = [];
        $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-list']) . ' ' . Yii::t('view', 'List Forums'), ['admin/new-forum', 'cid' => $category->id], ['class' => 'btn btn-default btn-xs']);
        $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']), ['admin/edit-category', 'id' => $category->id], ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('view', 'Edit Category')]);
        $actions[] = Html::tag('span', Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-trash']), ['class' => 'btn btn-danger btn-xs', 'data-url' => Url::to(['admin/delete-category', 'id' => $category->id]), 'data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('view', 'Delete Category')]);

        return Html::tag('p', implode(' ', $actions), ['class' => 'pull-right']) . Html::tag('span', Html::encode($category->name), ['class' => 'podium-forum', 'data-id' => $category->id]);
    }

    /**
     * Prepares content for forums administration.
     * @param mixed $forum
     * @return string
     */
    public static function adminForumsPrepareContent($forum)
    {
        $actions = [];
        $actions[] = Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-eye-' . ($forum->visible ? 'open' : 'close')]), ['class' => 'btn btn-xs text-muted', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => $forum->visible ? Yii::t('view', 'Forum visible for guests (if category is visible)') : Yii::t('view', 'Forum hidden for guests')]);
        $actions[] = Html::a(Html::tag('span', '', ['class' => 'glyphicon glyphicon-cog']), ['admin/edit-forum', 'id' => $forum->id, 'cid' => $forum->category_id], ['class' => 'btn btn-default btn-xs', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('view', 'Edit Forum')]);
        $actions[] = Html::tag('span', Html::button(Html::tag('span', '', ['class' => 'glyphicon glyphicon-trash']), ['class' => 'btn btn-danger btn-xs', 'data-url' => Url::to(['admin/delete-forum', 'id' => $forum->id, 'cid' => $forum->category_id]), 'data-toggle' => 'modal', 'data-target' => '#podiumModalDelete']), ['data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => Yii::t('view', 'Delete Forum')]);

        return Html::tag('p', implode(' ', $actions), ['class' => 'pull-right']) . Html::tag('span', Html::encode($forum->name), ['class' => 'podium-forum', 'data-id' => $forum->id, 'data-category' => $forum->category_id]);
    }

    /**
     * Returns image source for default avatar image in base64.
     * @return string image source
     */
    public static function defaultAvatar()
    {
        return '/img/default-profile.jpg';
    }

    /**
     * Returns user tag for deleted user.
     * @param bool $simple whether to return simple tag instead of full
     * @return string tag
     */
    public static function deletedUserTag($simple = false)
    {
        return static::podiumUserTag('', 0, null, null, $simple);
    }

    /**
     * Returns HTMLPurifier configuration set.
     * @param string $type set name
     * @return array configuration
     */
    public static function podiumPurifierConfig($type = '')
    {
        $config = [];

        switch ($type) {
            case 'full':
                $config = [
                    'HTML.Allowed' => 'p[class],br,b,strong,i,em,u,s,a[href|target],ul,li,ol,span[style|class],h1,h2,h3,h4,h5,h6,sub,sup,blockquote,pre[class],img[src|alt],iframe[class|frameborder|src],hr',
                    'CSS.AllowedProperties' => 'color,background-color',
                    'HTML.SafeIframe' => true,
                    'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%',
                    'Attr.AllowedFrameTargets' => ['_blank']
                ];
                break;
            case 'markdown':
                $config = [
                    'HTML.Allowed' => 'p,br,b,strong,i,em,u,s,a[href|target],ul,li,ol,hr,h1,h2,h3,h4,h5,h6,span,pre,code,table,tr,td,th,blockquote,img[src|alt]',
                    'Attr.AllowedFrameTargets' => ['_blank']
                ];
            case 'default':
            default:
                $config = [
                    'HTML.Allowed' => 'p[class],br,b,strong,i,em,u,s,a[href|target],ul,li,ol,hr',
                    'Attr.AllowedFrameTargets' => ['_blank']
                ];
        }

        return $config;
    }

    /**
     * Returns user tag.
     * @param string $name user name
     * @param int $role user role
     * @param int|null $id user ID
     * @param bool $simple whether to return simple tag instead of full
     * @return string tag
     */
    public static function podiumUserTag($name, $role, $id = null, $slug = null, $simple = false)
    {
        $icon = Html::tag('span', '', ['class' => $id ? 'glyphicon glyphicon-user' : 'glyphicon glyphicon-ban-circle']);
        $url = $id ? ['members/view', 'id' => $id, 'slug' => $slug] : '#';
        switch ($role) {
            case 0:
                $colourClass = 'text-muted';
                break;
            case User::ROLE_MODERATOR:
                $colourClass = 'text-info';
                break;
            case User::ROLE_ADMIN:
                $colourClass = 'text-danger';
                break;
            case User::ROLE_MEMBER:
            default:
                $colourClass = 'text-warning';
        }
        $encodedName = Html::tag('span', $icon . ' ' . ($id ? Html::encode($name) : Yii::t('view', 'user deleted')), ['class' => $colourClass]);

        if ($simple) {
            return $encodedName;
        }

        return Html::a($encodedName, $url, ['class' => 'btn btn-xs btn-default', 'data-pjax' => '0']);
        
    }

    /**
     * Returns quote HTML.
     * @param Post $post post model to be quoted
     * @param string $quote partial text to be quoted
     * @return string
     */
    public static function prepareQuote($post, $quote = '')
    {
        if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
            $content = !empty($quote) ? '[...] ' . HtmlPurifier::process($quote) . ' [...]' : $post->content;
            return '> ' . $post->author->podiumTag . ' @ ' . Podium::getInstance()->formatter->asDatetime($post->created_at) . "\n> " . $content . "\n";
        }
        $content = !empty($quote) ? '[...] ' . nl2br(HtmlPurifier::process($quote)) . ' [...]' : $post->content;
        return Html::tag('blockquote', $post->author->podiumTag . ' @ ' . Podium::getInstance()->formatter->asDatetime($post->created_at) . '<br>' . $content) . '<br>';
    }

    /**
     * Returns role label HTML.
     * @param int|null $role role ID
     * @return string
     */
    public static function roleLabel($role = null)
    {
        switch ($role) {
            case User::ROLE_ADMIN:
                $label = 'danger';
                $name = ArrayHelper::getValue(User::getRoles(), $role);
                break;
            case User::ROLE_MODERATOR:
                $label = 'info';
                $name = ArrayHelper::getValue(User::getRoles(), $role);
                break;
            default:
                $label = 'warning';
                $name = ArrayHelper::getValue(User::getRoles(), User::ROLE_MEMBER);
        }

        return Html::tag('span', $name, ['class' => 'label label-' . $label]);
    }

    /**
     * Returns sorting icon.
     * @param string|null $attribute sorting attribute name
     * @return string|null icon HTML or null if empty attribute
     */
    public static function sortOrder($attribute = null)
    {
        if (!empty($attribute)) {
            $sort = Yii::$app->request->get('sort');
            if ($sort == $attribute) {
                return ' ' . Html::tag('span', '', ['class' => 'glyphicon glyphicon-sort-by-alphabet']);
            }
            if ($sort == '-' . $attribute) {
                return ' ' . Html::tag('span', '', ['class' => 'glyphicon glyphicon-sort-by-alphabet-alt']);
            }
        }

        return null;
    }

    /**
     * Returns User status label.
     * @param int|null $status status ID
     * @return string label HTML
     */
    public static function statusLabel($status = null)
    {
        switch ($status) {
            case User::STATUS_ACTIVE:
                $label = 'info';
                $name = ArrayHelper::getValue(User::getStatuses(), $status);
                break;
            case User::STATUS_BANNED:
                $label = 'warning';
                $name = ArrayHelper::getValue(User::getStatuses(), $status);
                break;
            default:
                $label = 'default';
                $name = ArrayHelper::getValue(User::getStatuses(), User::STATUS_REGISTERED);
        }

        return Html::tag('span', $name, ['class' => 'label label-' . $label]);
    }

    /**
     * Returns time zones with current offset array.
     * @return array
     */
    public static function timeZones()
    {
        $timeZones = [];

        $timezone_identifiers = DateTimeZone::listIdentifiers();
        sort($timezone_identifiers);

        $timeZones['UTC'] = Yii::t('view', 'default (UTC)');

        foreach ($timezone_identifiers as $zone) {
            if ($zone != 'UTC') {
                $zoneName = $zone;
                $timeForZone = new DateTime(null, new DateTimeZone($zone));
                $offset = $timeForZone->getOffset();
                if (is_numeric($offset)) {
                    $zoneName .= ' (UTC';
                    if ($offset != 0) {
                        $offset = $offset / 60 / 60;
                        $offsetDisplay = floor($offset) . ':' . str_pad(60 * ($offset - floor($offset)), 2, '0', STR_PAD_LEFT);
                        $zoneName .= ' ' . ($offset < 0 ? '' : '+') . $offsetDisplay;
                    }
                    $zoneName .= ')';
                }
                $timeZones[$zone] = $zoneName;
            }
        }

        return $timeZones;
    }

    /**
     * Adds forum name to view title.
     * @param string $title
     * @return string
     */
    public static function title($title)
    {
        return $title . ' - ' . Podium::getInstance()->podiumConfig->get('name');
    }

    /**
     * Comparing versions.
     * @param array $a
     * @param array $b
     * @return string
     * @since 0.2
     */
    public static function compareVersions($a, $b)
    {
        $versionPos = max(count($a), count($b));
        while (count($a) < $versionPos) {
            $a[] = 0;
        }
        while (count($b) < $versionPos) {
            $b[] = 0;
        }

        for ($v = 0; $v < count($a); $v++) {
            if ((int)$a[$v] < (int)$b[$v]) {
                return '<';
            }
            if ((int)$a[$v] > (int)$b[$v]) {
                return '>';
            }
        }
        return '=';
    }
}
