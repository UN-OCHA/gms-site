<?php

namespace Drupal\html_export;

defined('DEFAULT_TARGET_CHARSET') || define('DEFAULT_TARGET_CHARSET', 'UTF-8');
defined('DEFAULT_BR_TEXT') || define('DEFAULT_BR_TEXT', "\r\n");
defined('DEFAULT_SPAN_TEXT') || define('DEFAULT_SPAN_TEXT', ' ');
defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 600000);
define('HDOM_SMARTY_AS_TEXT', 1);

/**
 * Get Html.
 */
function file_get_html(
  $url,
  $use_include_path = FALSE,
  $context = NULL,
  $offset = 0,
  $maxLen = -1,
  $lowercase = TRUE,
  $forceTagsClosed = TRUE,
  $target_charset = DEFAULT_TARGET_CHARSET,
  $stripRN = TRUE,
  $defaultBRText = DEFAULT_BR_TEXT,
  $defaultSpanText = DEFAULT_SPAN_TEXT) {
  if ($maxLen <= 0) {
    $maxLen = MAX_FILE_SIZE;
  }

  $dom = new SimpleHtmlDom(
    NULL,
    $lowercase,
    $forceTagsClosed,
    $target_charset,
    $stripRN,
    $defaultBRText,
    $defaultSpanText
  );
  $contents = file_get_contents(
    $url,
    $use_include_path,
    $context,
    $offset,
    $maxLen
  );
  // $contents = retrieve_url_contents($url);
  if (empty($contents) || strlen($contents) > $maxLen) {
    $dom->clear();
    return FALSE;
  }

  return $dom->load($contents, $lowercase, $stripRN);
}

/**
 * Get HTML.
 */
function str_get_html(
  $str,
  $lowercase = TRUE,
  $forceTagsClosed = TRUE,
  $target_charset = DEFAULT_TARGET_CHARSET,
  $stripRN = TRUE,
  $defaultBRText = DEFAULT_BR_TEXT,
  $defaultSpanText = DEFAULT_SPAN_TEXT) {
  $dom = new SimpleHtmlDom(
    NULL,
    $lowercase,
    $forceTagsClosed,
    $target_charset,
    $stripRN,
    $defaultBRText,
    $defaultSpanText
  );

  if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
    $dom->clear();
    return FALSE;
  }

  return $dom->load($str, $lowercase, $stripRN);
}

/**
 * Dump HTML tree.
 */
function dump_html_tree($node, $show_attr = TRUE, $deep = 0) {
  $node->dump($node);
}

/**
 * HTML dom class.
 */
class SimpleHtmlDom {
  /**
   * Root.
   *
   * @var string
   */
  public $root = NULL;
  /**
   * Nodes.
   *
   * @var array
   */
  public $nodes = [];
  /**
   * Callback.
   *
   * @var string
   */
  public $callback = NULL;
  /**
   * Lowercase.
   *
   * @var bool
   */
  public $lowercase = FALSE;
  /**
   * OriginalSize.
   *
   * @var string
   */
  public $originalSize;
  /**
   * Size.
   *
   * @var string
   */
  public $size;
  /**
   * Charset.
   *
   * @var string
   */
  public $charset = '';
  /**
   * TargetCharset.
   *
   * @var string
   */
  public $targetCharset = '';
  /**
   * DefaultSpanText.
   *
   * @var string
   */
  public $defaultSpanText = '';
  /**
   * Pos.
   *
   * @var string
   */
  protected $pos;
  /**
   * Doc.
   *
   * @var string
   */
  protected $doc;
  /**
   * Cursor.
   *
   * @var string
   */
  protected $char;
  /**
   * Cursor.
   *
   * @var string
   */
  protected $cursor;
  /**
   * Parent.
   *
   * @var string
   */
  protected $parent;
  /**
   * Noise.
   *
   * @var array
   */
  protected $noise = [];
  /**
   * TokenBlank.
   *
   * @var string
   */
  protected $tokenBlank = " \t\r\n";
  /**
   * TokenEqual.
   *
   * @var string
   */
  protected $tokenEqual = ' =/>';
  /**
   * TokenSlash.
   *
   * @var string
   */
  protected $tokenSlash = " />\r\n\t";
  /**
   * TokenAttr.
   *
   * @var string
   */
  protected $tokenAttr = ' >';
  /**
   * DefaultBrText.
   *
   * @var string
   */
  protected $defaultBrText = '';
  /**
   * SelfClosingTags.
   *
   * @var array
   */
  protected $selfClosingTags = [
    'area' => 1,
    'base' => 1,
    'br' => 1,
    'col' => 1,
    'embed' => 1,
    'hr' => 1,
    'img' => 1,
    'input' => 1,
    'link' => 1,
    'meta' => 1,
    'param' => 1,
    'source' => 1,
    'track' => 1,
    'wbr' => 1,
  ];
  /**
   * Blocktags.
   *
   * @var array
   */
  protected $blockTags = [
    'body' => 1,
    'div' => 1,
    'form' => 1,
    'root' => 1,
    'span' => 1,
    'table' => 1,
  ];
  /**
   * OptionalClosingTags.
   *
   * @var array
   */
  protected $optionalClosingTags = [
  // Not optional, see
  // https://www.w3.org/TR/html/textlevel-semantics.html#the-b-element
    'b' => ['b' => 1],
    'dd' => ['dd' => 1, 'dt' => 1],
  // Not optional, see
  // https://www.w3.org/TR/html/grouping-content.html#the-dl-element
    'dl' => ['dd' => 1, 'dt' => 1],
    'dt' => ['dd' => 1, 'dt' => 1],
    'li' => ['li' => 1],
    'optgroup' => ['optgroup' => 1, 'option' => 1],
    'option' => ['optgroup' => 1, 'option' => 1],
    'p' => ['p' => 1],
    'rp' => ['rp' => 1, 'rt' => 1],
    'rt' => ['rp' => 1, 'rt' => 1],
    'td' => ['td' => 1, 'th' => 1],
    'th' => ['td' => 1, 'th' => 1],
    'tr' => ['td' => 1, 'th' => 1, 'tr' => 1],
  ];

  /**
   * Constructor.
   */
  public function __construct(
  $str = NULL,
  $lowercase = TRUE,
  $forceTagsClosed = TRUE,
  $target_charset = DEFAULT_TARGET_CHARSET,
  $stripRN = TRUE,
  $defaultBRText = DEFAULT_BR_TEXT,
  $defaultSpanText = DEFAULT_SPAN_TEXT,
  $options = 0) {
    if ($str) {
      if (preg_match('/^http:\/\//i', $str) || is_file($str)) {
        $this->loadFileHtml($str);
      }
      else {
        $this->load(
        $str,
        $lowercase,
        $stripRN,
        $defaultBRText,
        $defaultSpanText,
        $options
        );
      }
    }
    // Forcing tags to be closed implies that we don't trust the html, but
    // it can lead to parsing errors if we SHOULD trust the html.
    if (!$forceTagsClosed) {
      $this->optional_closing_array = [];
    }

    $this->targetCharset = $target_charset;
  }

  /**
   * Load File.
   */
  public function loadFileHtml() {
    $args = func_get_args();

    if (($doc = call_user_func_array('file_get_contents', $args)) !== FALSE) {
      $this->load($doc, TRUE);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Load html.
   */
  public function load(
  $str,
  $lowercase = TRUE,
  $stripRN = TRUE,
  $defaultBRText = DEFAULT_BR_TEXT,
  $defaultSpanText = DEFAULT_SPAN_TEXT,
  $options = 0) {
    global $_debug_object;

    // Prepare.
    $this->prepare($str, $lowercase, $defaultBRText, $defaultSpanText);

    // Per sourceforge http://sourceforge.net/tracker/?func=detail&aid=2949097&group_id=218559&atid=1044037
    // Script tags removal now preceeds style tag removal.
    // strip out <script> tags.
    $this->removeNoise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
    $this->removeNoise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");

    // Strip out the \r \n's if we are told to.
    if ($stripRN) {
      $this->doc = str_replace("\r", ' ', $this->doc);
      $this->doc = str_replace("\n", ' ', $this->doc);

      // Set the length of content since we have changed it.
      $this->size = strlen($this->doc);
    }

    // Strip out cdata.
    $this->removeNoise("'<!\[CDATA\[(.*?)\]\]>'is", TRUE);
    // Strip out comments.
    $this->removeNoise("'<!--(.*?)-->'is");
    // Strip out <style> tags.
    $this->removeNoise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
    $this->removeNoise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
    // Strip out preformatted tags.
    $this->removeNoise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
    // Strip out server side scripts.
    $this->removeNoise("'(<\?)(.*?)(\?>)'s", TRUE);

    // Strip Smarty scripts.
    if ($options & HDOM_SMARTY_AS_TEXT) {
      $this->removeNoise("'(\{\w)(.*?)(\})'s", TRUE);
    }

    // Parsing.
    $this->parse();
    // End.
    $this->root->blank[HDOM_INFO_END] = $this->cursor;
    $this->parseCharset();

    // Make load function chainable.
    return $this;
  }

  /**
   * Prepare data.
   */
  protected function prepare(
        $str,
        $lowercase = TRUE,
        $defaultBRText = DEFAULT_BR_TEXT,
        $defaultSpanText = DEFAULT_SPAN_TEXT) {
    $this->clear();

    $this->doc = trim($str);
    $this->size = strlen($this->doc);
    // Original size of the html.
    $this->originalSize = $this->size;
    $this->pos = 0;
    $this->cursor = 1;
    $this->noise = [];
    $this->nodes = [];
    $this->lowercase = $lowercase;
    $this->defaultBrText = $defaultBRText;
    $this->defaultSpanText = $defaultSpanText;
    $this->root = new SimpleHtmlDomNode($this);
    $this->root->tag = 'root';
    $this->root->blank[HDOM_INFO_BEGIN] = -1;
    $this->root->nodetype = HDOM_TYPE_ROOT;
    $this->parent = $this->root;
    if ($this->size > 0) {
      $this->char = $this->doc[0];
    }
  }

  /**
   * Clear data.
   */
  public function clear() {
    if (isset($this->nodes)) {
      foreach ($this->nodes as $n) {
        $n->clear();
        $n = NULL;
      }
    }

    // This add next line is documented in the sourceforge repository.
    // 2977248 as a fix for ongoing memory leaks that occur even with the
    // use of clear.
    if (isset($this->children)) {
      foreach ($this->children as $n) {
        $n->clear();
        $n = NULL;
      }
    }

    if (isset($this->parent)) {
      $this->parent->clear();
      unset($this->parent);
    }

    if (isset($this->root)) {
      $this->root->clear();
      unset($this->root);
    }

    unset($this->doc);
    unset($this->noise);
  }

  /**
   * Remove noise.
   */
  protected function removeNoise($pattern, $remove_tag = FALSE) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    $count = preg_match_all(
          $pattern,
          $this->doc,
          $matches,
          PREG_SET_ORDER | PREG_OFFSET_CAPTURE
      );

    for ($i = $count - 1; $i > -1; --$i) {
      $key = '___noise___' . sprintf('% 5d', count($this->noise) + 1000);

      if (is_object($_debug_object)) {
        $_debug_object->debug_log(2, 'key is: ' . $key);
      }

      // 0 = entire match, 1 = submatch
      $idx = ($remove_tag) ? 0 : 1;
      $this->noise[$key] = $matches[$i][$idx][0];
      $this->doc = substr_replace($this->doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
    }

    // Reset the length of content.
    $this->size = strlen($this->doc);

    if ($this->size > 0) {
      $this->char = $this->doc[0];
    }
  }

  /**
   * Parse.
   */
  protected function parse() {
    while (TRUE) {
      // Read next tag if there is no text between current position and the
      // next opening tag.
      if (($s = $this->copyUntilChar('<')) === '') {
        if ($this->readTag()) {
          continue;
        }
        else {
          return TRUE;
        }
      }

      // Add a text node for text between tags.
      $node = new SimpleHtmlDomNode($this);
      ++$this->cursor;
      $node->blank[HDOM_INFO_TEXT] = $s;
      $this->linkNodes($node, FALSE);
    }
  }

  /**
   * Copy untill char.
   */
  protected function copyUntilChar($char) {
    if ($this->char === NULL) {
      return '';
    }

    if (($pos = strpos($this->doc, $char, $this->pos)) === FALSE) {
      $ret = substr($this->doc, $this->pos, $this->size - $this->pos);
      $this->char = NULL;
      $this->pos = $this->size;
      return $ret;
    }

    if ($pos === $this->pos) {
      return '';
    }

    $pos_old = $this->pos;
    $this->char = $this->doc[$pos];
    $this->pos = $pos;
    return substr($this->doc, $pos_old, $pos - $pos_old);
  }

  /**
   * Read tags.
   */
  protected function readTag() {
    // Set end position if no further tags found.
    if ($this->char !== '<') {
      $this->root->blank[HDOM_INFO_END] = $this->cursor;
      return FALSE;
    }

    $begin_tag_pos = $this->pos;
    // Next.
    $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;

    // End tag.
    if ($this->char === '/') {
      // Next.
      $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;

      // Skip whitespace in end tags (i.e. in "</   html>")
      $this->skip($this->tokenBlank);
      $tag = $this->copyUntilChar('>');

      // Skip attributes in end tags.
      if (($pos = strpos($tag, ' ')) !== FALSE) {
        $tag = substr($tag, 0, $pos);
      }

      $parent_lower = strtolower($this->parent->tag);
      $tag_lower = strtolower($tag);

      // The end tag is supposed to close the parent tag. Handle situations
      // when it doesn't.
      if ($parent_lower !== $tag_lower) {
        // Parent tag does not have to be closed necessarily
        // (optional closing tag)
        // Current tag is a block tag, so it may close an ancestor.
        if (isset($this->optionalClosingTags[$parent_lower])
              && isset($this->blockTags[$tag_lower])) {

          $this->parent->blank[HDOM_INFO_END] = 0;
          $org_parent = $this->parent;

          // Traverse ancestors to find a matching opening tag
          // Stop at root node.
          while (($this->parent->parent)
                && strtolower($this->parent->tag) !== $tag_lower
                ) {
            $this->parent = $this->parent->parent;
          }

          // If we don't have a match add current tag as text node.
          if (strtolower($this->parent->tag) !== $tag_lower) {
            // Restore origonal parent.
            $this->parent = $org_parent;

            if ($this->parent->parent) {
              $this->parent = $this->parent->parent;
            }

            $this->parent->blank[HDOM_INFO_END] = $this->cursor;
            return $this->asTextNode($tag);
          }
        }
        elseif (($this->parent->parent)
              && isset($this->blockTags[$tag_lower])
          ) {
          // Grandparent exists and current tag is a block tag, so our
          // parent doesn't have an end tag.
          // No end tag.
          $this->parent->blank[HDOM_INFO_END] = 0;
          $org_parent = $this->parent;

          // Traverse ancestors to find a matching opening tag
          // Stop at root node.
          while (($this->parent->parent)
                && strtolower($this->parent->tag) !== $tag_lower
                ) {
            $this->parent = $this->parent->parent;
          }

          // If we don't have a match add current tag as text node.
          if (strtolower($this->parent->tag) !== $tag_lower) {
            // Restore origonal parent.
            $this->parent = $org_parent;
            $this->parent->blank[HDOM_INFO_END] = $this->cursor;
            return $this->asTextNode($tag);
          }
        }
        elseif (($this->parent->parent)
              && strtolower($this->parent->parent->tag) === $tag_lower
              // Grandparent exists and current tag closes it.
          ) {
          $this->parent->blank[HDOM_INFO_END] = 0;
          $this->parent = $this->parent->parent;
        }
        // Random tag, add as text node.
        else {
          return $this->asTextNode($tag);
        }
      }

      // Set end position of parent tag to current cursor position.
      $this->parent->blank[HDOM_INFO_END] = $this->cursor;

      if ($this->parent->parent) {
        $this->parent = $this->parent->parent;
      }

      // Next.
      $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
      return TRUE;
    }

    // Start tag.
    $node = new SimpleHtmlDomNode($this);
    $node->blank[HDOM_INFO_BEGIN] = $this->cursor;
    ++$this->cursor;
    // Get tag name.
    $tag = $this->copyUntil($this->tokenSlash);
    $node->tag_start = $begin_tag_pos;

    // doctype, cdata & comments...
    // <!DOCTYPE html>
    // <![CDATA[ ... ]]>
    // <!-- Comment -->.
    if (isset($tag[0]) && $tag[0] === '!') {
      $node->blank[HDOM_INFO_TEXT] = '<' . $tag . $this->copyUntilChar('>');

      // Comment ("<!--")
      if (isset($tag[2]) && $tag[1] === '-' && $tag[2] === '-') {
        $node->nodetype = HDOM_TYPE_COMMENT;
        $node->tag = 'comment';
      }
      // Could be doctype or CDATA but we don't care.
      else {
        $node->nodetype = HDOM_TYPE_UNKNOWN;
        $node->tag = 'unknown';
      }

      if ($this->char === '>') {
        $node->blank[HDOM_INFO_TEXT] .= '>';
      }

      $this->linkNodes($node, TRUE);
      // Next.
      $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
      return TRUE;
    }

    // The start tag cannot contain another start tag, if so add as text
    // i.e. "<<html>".
    if ($pos = strpos($tag, '<') !== FALSE) {
      $tag = '<' . substr($tag, 0, -1);
      $node->blank[HDOM_INFO_TEXT] = $tag;
      $this->linkNodes($node, FALSE);
      // Prev.
      $this->char = $this->doc[--$this->pos];
      return TRUE;
    }

    // Handle invalid tag names (i.e. "<html#doc>")
    if (!preg_match('/^\w[\w:-]*$/', $tag)) {
      $node->blank[HDOM_INFO_TEXT] = '<' . $tag . $this->copyUntil('<>');

      // Next char is the beginning of a new tag, don't touch it.
      if ($this->char === '<') {
        $this->linkNodes($node, FALSE);
        return TRUE;
      }

      // Next char closes current tag, add and be done with it.
      if ($this->char === '>') {
        $node->blank[HDOM_INFO_TEXT] .= '>';
      }
      $this->linkNodes($node, FALSE);
      // Next.
      $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
      return TRUE;
    }

    // Begin tag, add new node.
    $node->nodetype = HDOM_TYPE_ELEMENT;
    $tag_lower = strtolower($tag);
    $node->tag = ($this->lowercase) ? $tag_lower : $tag;

    // Handle optional closing tags.
    if (isset($this->optionalClosingTags[$tag_lower])) {
      // Traverse ancestors to close all optional closing tags.
      while (isset($this->optionalClosingTags[$tag_lower][strtolower($this->parent->tag)])) {
        $this->parent->blank[HDOM_INFO_END] = 0;
        $this->parent = $this->parent->parent;
      }
      $node->parent = $this->parent;
    }

    // Prevent infinity loop.
    $guard = 0;

    // [0] Space between tag and first attribute
    $space = [$this->copySkip($this->tokenBlank), '', ''];

    // Attributes.
    do {
      // Everything until the first equal sign should be the attribute name.
      $name = $this->copyUntil($this->tokenEqual);

      if ($name === '' && $this->char !== NULL && $space[0] === '') {
        break;
      }

      // Escape infinite loop.
      if ($guard === $this->pos) {
        // Next.
        $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
        continue;
      }

      $guard = $this->pos;

      // Handle endless '<'
      // Out of bounds before the tag ended.
      if ($this->pos >= $this->size - 1 && $this->char !== '>') {
        $node->nodetype = HDOM_TYPE_TEXT;
        $node->blank[HDOM_INFO_END] = 0;
        $node->blank[HDOM_INFO_TEXT] = '<' . $tag . $space[0] . $name;
        $node->tag = 'text';
        $this->linkNodes($node, FALSE);
        return TRUE;
      }

      // Handle mismatch '<'
      // Attributes cannot start after opening tag.
      if ($this->doc[$this->pos - 1] == '<') {
        $node->nodetype = HDOM_TYPE_TEXT;
        $node->tag = 'text';
        $node->attr = [];
        $node->blank[HDOM_INFO_END] = 0;
        $node->blank[HDOM_INFO_TEXT] = substr(
              $this->doc,
              $begin_tag_pos,
              $this->pos - $begin_tag_pos - 1
          );
        $this->pos -= 2;
        // Next.
        $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
        $this->linkNodes($node, FALSE);
        return TRUE;
      }

      // This is a attribute name.
      if ($name !== '/' && $name !== '') {
        // [1] Whitespace after attribute name.
        $space[1] = $this->copySkip($this->tokenBlank);

        // Might be a noisy name.
        $name = $this->restoreNoise($name);

        if ($this->lowercase) {
          $name = strtolower($name);
        }

        // Attribute with value.
        if ($this->char === '=') {
          // Next.
          $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
          // Get attribute value.
          $this->parseAttr($node, $name, $space);
        }
        else {
          // No value attr: nowrap, checked selected...
          $node->blank[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
          $node->attr[$name] = TRUE;
          if ($this->char != '>') {
            $this->char = $this->doc[--$this->pos];
          } // prev
        }

        $node->blank[HDOM_INFO_SPACE][] = $space;

        // Prepare for next attribute.
        $space = [
          $this->copySkip($this->tokenBlank),
          '',
          '',
        ];
      }
      // No more attributes.
      else {
        break;
      }
      // Go until the tag ended.
    } while ($this->char !== '>' && $this->char !== '/');

    $this->linkNodes($node, TRUE);
    $node->blank[HDOM_INFO_ENDSPACE] = $space[0];

    // Handle empty tags (i.e. "<div/>")
    if ($this->copyUntilChar('>') === '/') {
      $node->blank[HDOM_INFO_ENDSPACE] .= '/';
      $node->blank[HDOM_INFO_END] = 0;
    }
    else {
      // Reset parent.
      if (!isset($this->selfClosingTags[strtolower($node->tag)])) {
        $this->parent = $node;
      }
    }

    // Next.
    $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;

    // If it's a BR tag, we need to set it's text to the default text.
    // This way when we see it in plaintext,
    // we can generate formatting that the user wants.
    // since a br tag never has sub nodes, this works well.
    if ($node->tag === 'br') {
      $node->blank[HDOM_INFO_INNER] = $this->defaultBrText;
    }

    return TRUE;
  }

  /**
   * Skip form specfic charector.
   */
  protected function skip($chars) {
    $this->pos += strspn($this->doc, $chars, $this->pos);
    // Next.
    $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
  }

  /**
   * Get text.
   */
  protected function asTextNode($tag) {
    $node = new SimpleHtmlDomNode($this);
    ++$this->cursor;
    $node->blank[HDOM_INFO_TEXT] = '</' . $tag . '>';
    $this->linkNodes($node, FALSE);
    // Next.
    $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
    return TRUE;
  }

  /**
   * Get link nodes.
   */
  protected function linkNodes(&$node, $is_child) {
    $node->parent = $this->parent;
    $this->parent->nodes[] = $node;
    if ($is_child) {
      $this->parent->children[] = $node;
    }
  }

  /**
   * Copy till specific character.
   */
  protected function copyUntil($chars) {
    $pos = $this->pos;
    $len = strcspn($this->doc, $chars, $pos);
    $this->pos += $len;
    // Next.
    $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
    return substr($this->doc, $pos, $len);
  }

  /**
   * Skip copy.
   */
  protected function copySkip($chars) {
    $pos = $this->pos;
    $len = strspn($this->doc, $chars, $pos);
    $this->pos += $len;
    // Next.
    $this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
    if ($len === 0) {
      return '';
    }
    return substr($this->doc, $pos, $len);
  }

  /**
   * Restore.
   */
  public function restoreNoise($text) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    while (($pos = strpos($text, '___noise___')) !== FALSE) {
      // Sometimes there is a broken piece of markup, and we don't GET the
      // pos+11 etc... token which indicates a problem outside of us...
      // @todo "___noise___1000" (or any number with four or more digits)
      // in the DOM causes an infinite loop which could be utilized by
      // malicious software.
      if (strlen($text) > $pos + 15) {
        $key = '___noise___'
                    . $text[$pos + 11]
                    . $text[$pos + 12]
                    . $text[$pos + 13]
                    . $text[$pos + 14]
                    . $text[$pos + 15];

        if (is_object($_debug_object)) {
          $_debug_object->debug_log(2, 'located key of: ' . $key);
        }

        if (isset($this->noise[$key])) {
          $text = substr($text, 0, $pos)
                        . $this->noise[$key]
                        . substr($text, $pos + 16);
        }
        else {
          // Do this to prevent an infinite loop.
          $text = substr($text, 0, $pos)
                        . 'UNDEFINED NOISE FOR KEY: '
                        . $key
                        . substr($text, $pos + 16);
        }
      }
      else {
        // There is no valid key being given back to us... We must get
        // rid of the ___noise___ or we will have a problem.
        $text = substr($text, 0, $pos)
                    . 'NO NUMERIC NOISE KEY'
                    . substr($text, $pos + 11);
      }
    }
    return $text;
  }

  /**
   * Parse attributes.
   */
  protected function parseAttr($node, $name, &$space) {
    $is_duplicate = isset($node->attr[$name]);

    // Copy whitespace between "=" and value.
    if (!$is_duplicate) {
      $space[2] = $this->copySkip($this->tokenBlank);
    }

    switch ($this->char) {
      case '"':
        $quote_type = HDOM_QUOTE_DOUBLE;
        // Next.
        $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
        $value = $this->copyUntilChar('"');
        // Next.
        $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
        break;

      case '\'':
        $quote_type = HDOM_QUOTE_SINGLE;
        // Next.
        $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
        $value = $this->copyUntilChar('\'');
        // Next.
        $this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : NULL;
        break;

      default:
        $quote_type = HDOM_QUOTE_NO;
        $value = $this->copyUntil($this->tokenAttr);
    }

    $value = $this->restoreNoise($value);

    // PaperG: Attributes should not have \r or \n in them, that counts as
    // html whitespace.
    $value = str_replace("\r", '', $value);
    $value = str_replace("\n", '', $value);

    // PaperG: If this is a "class" selector, lets get rid of the preceeding
    // and trailing space since some people leave it in the multi class case.
    if ($name === 'class') {
      $value = trim($value);
    }

    if (!$is_duplicate) {
      $node->blank[HDOM_INFO_QUOTE][] = $quote_type;
      $node->attr[$name] = $value;
    }
  }

  /**
   * Parse charset.
   */
  protected function parseCharset() {
    global $_debug_object;

    $charset = NULL;

    if (function_exists('get_last_retrieve_url_contents_content_type')) {
      $contentTypeHeader = get_last_retrieve_url_contents_content_type();
      $success = preg_match('/charset=(.+)/', $contentTypeHeader, $matches);
      if ($success) {
        $charset = $matches[1];
        if (is_object($_debug_object)) {
          $_debug_object->debug_log(2,
                'header content-type found charset of: '
                    . $charset
            );
        }
      }
    }

    if (empty($charset)) {
      // https://www.w3.org/TR/html/document-metadata.html#statedef-http-equiv-content-type
      $el = $this->root->find('meta[http-equiv=Content-Type]', 0, TRUE);

      if (!empty($el)) {
        $fullvalue = $el->content;
        if (is_object($_debug_object)) {
          $_debug_object->debug_log(2,
                'meta content-type tag found'
                    . $fullvalue
            );
        }

        if (!empty($fullvalue)) {
          $success = preg_match(
                '/charset=(.+)/i',
                $fullvalue,
                $matches
            );

          if ($success) {
            $charset = $matches[1];
          }
          else {
            // If there is a meta tag, and they don't specify the
            // character set, research says that it's typically
            // ISO-8859-1.
            if (is_object($_debug_object)) {
              $_debug_object->debug_log(2,
                    'meta content-type tag couldn\'t be parsed. using iso-8859 default.'
                );
            }

            $charset = 'ISO-8859-1';
          }
        }
      }
    }

    if (empty($charset)) {
      // https://www.w3.org/TR/html/document-metadata.html#character-encoding-declaration
      if ($meta = $this->root->find('meta[charset]', 0)) {
        $charset = $meta->charset;
        if (is_object($_debug_object)) {
          $_debug_object->debug_log(2, 'meta charset: ' . $charset);
        }
      }
    }

    if (empty($charset)) {
      // Try to guess the charset based on the content
      // Requires Multibyte String (mbstring) support (optional)
      if (function_exists('mb_detect_encoding')) {
        $encoding = mb_detect_encoding(
              $this->doc,
              ['UTF-8', 'CP1252', 'ISO-8859-1']
          );

        if ($encoding === 'CP1252' || $encoding === 'ISO-8859-1') {
          // Due to a limitation of mb_detect_encoding
          // 'CP1251'/'ISO-8859-5' will be detected as
          // 'CP1252'/'ISO-8859-1'. This will cause iconv to fail, in
          // which case we can simply assume it is the other charset.
          if (!@iconv('CP1252', 'UTF-8', $this->doc)) {
            $encoding = 'CP1251';
          }
        }

        if ($encoding !== FALSE) {
          $charset = $encoding;
          if (is_object($_debug_object)) {
            $_debug_object->debug_log(2, 'mb_detect: ' . $charset);
          }
        }
      }
    }

    if (empty($charset)) {
      // Assume it's UTF-8 as it is the most likely charset to be used.
      $charset = 'UTF-8';
      if (is_object($_debug_object)) {
        $_debug_object->debug_log(2, 'No match found, assume ' . $charset);
      }
    }

    // Since CP1252 is a superset, if we get one of it's subsets, we want
    // it instead.
    if ((strtolower($charset) == 'iso-8859-1')
          || (strtolower($charset) == 'latin1')
          || (strtolower($charset) == 'latin-1')) {
      $charset = 'CP1252';
      if (is_object($_debug_object)) {
        $_debug_object->debug_log(2,
              'replacing ' . $charset . ' with CP1252 as its a superset'
        );
      }
    }

    if (is_object($_debug_object)) {
      $_debug_object->debug_log(1, 'EXIT - ' . $charset);
    }

    return $this->charset = $charset;
  }

  /**
   * Distractor.
   */
  public function __destruct() {
    $this->clear();
  }

  /**
   * Set Callback.
   */
  public function setCallback($function_name) {
    $this->callback = $function_name;
  }

  /**
   * Remove call back.
   */
  public function removeCallback() {
    $this->callback = NULL;
  }

  /**
   * Save Html.
   */
  public function save($filepath = '') {
    $ret = $this->root->innertext();
    if ($filepath !== '') {
      file_put_contents($filepath, $ret, LOCK_EX);
    }
    return $ret;
  }

  /**
   * Dump data.
   */
  public function dump($show_attr = TRUE) {
    $this->root->dump($show_attr);
  }

  /**
   * Search text.
   */
  public function searchNoise($text) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    foreach ($this->noise as $noiseElement) {
      if (strpos($noiseElement, $text) !== FALSE) {
        return $noiseElement;
      }
    }
  }

  /**
   * Get Text to string.
   */
  public function __toString() {
    return $this->root->innertext();
  }

  /**
   * Get Text of element.
   */
  public function __get($name) {
    switch ($name) {
      case 'outertext':
        return $this->root->innertext();

      case 'innertext':
        return $this->root->innertext();

      case 'plaintext':
        return $this->root->text();

      case 'charset':
        return $this->charset;

      case 'target_charset':
        return $this->targetCharset;
    }
  }

  /**
   * Get child nodes.
   */
  public function childNodes($idx = -1) {
    return $this->root->childNodes($idx);
  }

  /**
   * Get Last child.
   */
  public function lastChild() {
    return $this->root->lastChilds();
  }

  /**
   * Create Element.
   */
  public function createElement($name, $value = NULL) {
    return @str_get_html("<$name>$value</$name>")->firstChild();
  }

  /**
   * Get first child.
   */
  public function firstChild() {
    return $this->root->firstChilds();
  }

  /**
   * Get Text of element.
   */
  public function createTextNode($value) {
    return @end(str_get_html($value)->nodes);
  }

  /**
   * Get Element by Id.
   */
  public function getElementById($id) {
    return $this->find("#$id", 0);
  }

  /**
   * Find element.
   */
  public function find($selector, $idx = NULL, $lowercase = FALSE) {
    return $this->root->find($selector, $idx, $lowercase);
  }

  /**
   * Get Elements by Id.
   */
  public function getElementsById($id, $idx = NULL) {
    return $this->find("#$id", $idx);
  }

  /**
   * Get Single element by tag name.
   */
  public function getElementByTagName($name) {
    return $this->find($name, 0);
  }

  /**
   * Get Multile elements by tag name.
   */
  public function getElementsByTagName($name, $idx = -1) {
    return $this->find($name, $idx);
  }

  /**
   * Load file.
   */
  public function loadFile() {
    $args = func_get_args();
    $this->loadFileHtml($args);
  }

}
