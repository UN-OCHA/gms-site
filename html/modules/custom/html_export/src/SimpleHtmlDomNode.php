<?php

namespace Drupal\html_export;

define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT', 3);
define('HDOM_TYPE_ENDTAG', 4);
define('HDOM_TYPE_ROOT', 5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO', 3);
define('HDOM_INFO_BEGIN', 0);
define('HDOM_INFO_END', 1);
define('HDOM_INFO_QUOTE', 2);
define('HDOM_INFO_SPACE', 3);
define('HDOM_INFO_TEXT', 4);
define('HDOM_INFO_INNER', 5);
define('HDOM_INFO_OUTER', 6);
define('HDOM_INFO_ENDSPACE', 7);

defined('DEFAULT_TARGET_CHARSET') || define('DEFAULT_TARGET_CHARSET', 'UTF-8');
defined('DEFAULT_BR_TEXT') || define('DEFAULT_BR_TEXT', "\r\n");
defined('DEFAULT_SPAN_TEXT') || define('DEFAULT_SPAN_TEXT', ' ');
defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 600000);

/**
 * Simple HTML dom node.
 */
class SimpleHtmlDomNode {
  /**
   * Nodetype.
   *
   * @var int
   */
  public $nodetype = HDOM_TYPE_TEXT;
  /**
   * Tag.
   *
   * @var string
   */
  public $tag = 'text';
  /**
   * Attr.
   *
   * @var array
   */
  public $attr = [];
  /**
   * Children.
   *
   * @var array
   */
  public $children = [];
  /**
   * Node.
   *
   * @var array
   */
  public $nodes = [];
  /**
   * Parent.
   *
   * @var string
   */
  public $parent = NULL;
  /**
   * Blank array.
   *
   * @var array
   */
  public $blank = [];
  /**
   * TagStart.
   *
   * @var int
   */
  public $tagStart = 0;
  /**
   * Dom.
   *
   * @var string
   */
  private $dom = NULL;

  /**
   * Constructor.
   */
  public function __construct($dom) {
    $this->dom = $dom;
    $dom->nodes[] = $this;
  }

  /**
   * Destructor.
   */
  public function __destruct() {
    $this->clear();
  }

  /**
   * To string.
   */
  public function __toString() {
    return $this->outertext();
  }

  /**
   * Clear.
   */
  public function clear() {
    $this->dom = NULL;
    $this->nodes = NULL;
    $this->parent = NULL;
    $this->children = NULL;
  }

  /**
   * Dump.
   */
  public function dump($show_attr = TRUE, $depth = 0) {
    echo str_repeat("\t", $depth) . $this->tag;

    if ($show_attr && count($this->attr) > 0) {
      echo '(';
      foreach ($this->attr as $k => $v) {
        echo "[$k]=>\"$v\", ";
      }
      echo ')';
    }

    echo "\n";

    if ($this->nodes) {
      foreach ($this->nodes as $node) {
        $node->dump($show_attr, $depth + 1);
      }
    }
  }

  /**
   * Dump Node.
   */
  public function dumpNode($echo = TRUE) {
    $string = $this->tag;

    if (count($this->attr) > 0) {
      $string .= '(';
      foreach ($this->attr as $k => $v) {
        $string .= "[$k]=>\"$v\", ";
      }
      $string .= ')';
    }

    if (count($this->blank) > 0) {
      $string .= ' $_ (';
      foreach ($this->blank as $k => $v) {
        if (is_array($v)) {
          $string .= "[$k]=>(";
          foreach ($v as $k2 => $v2) {
            $string .= "[$k2]=>\"$v2\", ";
          }
          $string .= ')';
        }
        else {
          $string .= "[$k]=>\"$v\", ";
        }
      }
      $string .= ')';
    }

    if (isset($this->text)) {
      $string .= " text: ({$this->text})";
    }

    $string .= ' HDOM_INNER_INFO: ';

    if (isset($node->blank[HDOM_INFO_INNER])) {
      $string .= "'" . $node->blank[HDOM_INFO_INNER] . "'";
    }
    else {
      $string .= ' NULL ';
    }

    $string .= ' children: ' . count($this->children);
    $string .= ' nodes: ' . count($this->nodes);
    $string .= ' tagStart: ' . $this->tagStart;
    $string .= "\n";

    if ($echo) {
      echo $string;
      return;
    }
    else {
      return $string;
    }
  }

  /**
   * Parent.
   */
  public function parent($parent = NULL) {
    // I am SURE that this doesn't work properly.
    // It fails to unset the current node from it's current parents nodes or
    // children list first.
    if ($parent !== NULL) {
      $this->parent = $parent;
      $this->parent->nodes[] = $this;
      $this->parent->children[] = $this;
    }

    return $this->parent;
  }

  /**
   * Has child.
   */
  public function hasChild() {
    return !empty($this->children);
  }

  /**
   * Children.
   */
  public function children($idx = -1) {
    if ($idx === -1) {
      return $this->children;
    }

    if (isset($this->children[$idx])) {
      return $this->children[$idx];
    }

    return NULL;
  }

  /**
   * Get first child.
   */
  public function firstChilds() {
    if (count($this->children) > 0) {
      return $this->children[0];
    }
    return NULL;
  }

  /**
   * Get Last child.
   */
  public function lastChilds() {
    if (count($this->children) > 0) {
      return end($this->children);
    }
    return NULL;
  }

  /**
   * Next Siblings.
   */
  public function nextSiblings() {
    if ($this->parent === NULL) {
      return NULL;
    }

    $idx = array_search($this, $this->parent->children, TRUE);

    if ($idx !== FALSE && isset($this->parent->children[$idx + 1])) {
      return $this->parent->children[$idx + 1];
    }

    return NULL;
  }

  /**
   * Get Prev Sibling.
   */
  public function prevSibling() {
    if ($this->parent === NULL) {
      return NULL;
    }

    $idx = array_search($this, $this->parent->children, TRUE);

    if ($idx !== FALSE && $idx > 0) {
      return $this->parent->children[$idx - 1];
    }

    return NULL;
  }

  /**
   * Find ancestor tag.
   */
  public function findAncestorTag($tag) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    if ($this->parent === NULL) {
      return NULL;
    }

    $ancestor = $this->parent;

    while (!is_null($ancestor)) {
      if (is_object($_debug_object)) {
        $_debug_object->debug_log(2, 'Current tag is: ' . $ancestor->tag);
      }

      if ($ancestor->tag === $tag) {
        break;
      }

      $ancestor = $ancestor->parent;
    }

    return $ancestor;
  }

  /**
   * Inner Text.
   */
  public function innertext() {
    if (isset($this->blank[HDOM_INFO_INNER])) {
      return $this->blank[HDOM_INFO_INNER];
    }

    if (isset($this->blank[HDOM_INFO_TEXT])) {
      return $this->dom->restoreNoise($this->blank[HDOM_INFO_TEXT]);
    }

    $ret = '';

    foreach ($this->nodes as $n) {
      $ret .= $n->outertext();
    }

    return $ret;
  }

  /**
   * Outer text.
   */
  public function outertext() {
    global $_debug_object;

    if (is_object($_debug_object)) {
      $text = '';

      if ($this->tag === 'text') {
        if (!empty($this->text)) {
          $text = ' with text: ' . $this->text;
        }
      }

      $_debug_object->debug_log(1, 'Innertext of tag: ' . $this->tag . $text);
    }

    if ($this->tag === 'root') {
      return $this->innertext();
    }

    // @todo What is the use of this callback? Remove?
    if ($this->dom && $this->dom->callback !== NULL) {
      call_user_func_array($this->dom->callback, [$this]);
    }

    if (isset($this->blank[HDOM_INFO_OUTER])) {
      return $this->blank[HDOM_INFO_OUTER];
    }

    if (isset($this->blank[HDOM_INFO_TEXT])) {
      return $this->dom->restoreNoise($this->blank[HDOM_INFO_TEXT]);
    }

    $ret = '';

    if ($this->dom && $this->dom->nodes[$this->blank[HDOM_INFO_BEGIN]]) {
      $ret = $this->dom->nodes[$this->blank[HDOM_INFO_BEGIN]]->makeup();
    }

    if (isset($this->blank[HDOM_INFO_INNER])) {
      // @todo <br> should either never have HDOM_INFO_INNER or always.
      if ($this->tag !== 'br') {
        $ret .= $this->blank[HDOM_INFO_INNER];
      }
    }
    elseif ($this->nodes) {
      foreach ($this->nodes as $n) {
        $ret .= $this->convertText($n->outertext());
      }
    }

    if (isset($this->blank[HDOM_INFO_END]) && $this->blank[HDOM_INFO_END] != 0) {
      $ret .= '</' . $this->tag . '>';
    }

    return $ret;
  }

  /**
   * Text.
   */
  public function text() {
    if (isset($this->blank[HDOM_INFO_INNER])) {
      return $this->blank[HDOM_INFO_INNER];
    }

    switch ($this->nodetype) {
      case HDOM_TYPE_TEXT:
        return $this->dom->restoreNoise($this->blank[HDOM_INFO_TEXT]);

      case HDOM_TYPE_COMMENT:
        return '';

      case HDOM_TYPE_UNKNOWN:
        return '';
    }

    if (strcasecmp($this->tag, 'script') === 0) {
      return '';
    }
    if (strcasecmp($this->tag, 'style') === 0) {
      return '';
    }

    $ret = '';

    // In rare cases, (always node type 1 or HDOM_TYPE_ELEMENT - observed
    // for some span tags, and some p tags) $this->nodes is set to NULL.
    // NOTE: This indicates that there is a problem where it's set to NULL
    // without a clear happening.
    // WHY is this happening?
    if (!is_null($this->nodes)) {
      foreach ($this->nodes as $n) {
        // Start paragraph after a blank line.
        if ($n->tag === 'p') {
          $ret = trim($ret) . "\n\n";
        }

        $ret .= $this->convertText($n->text());

        // If this node is a span... add a space at the end of it so
        // multiple spans don't run into each other.  This is plaintext
        // after all.
        if ($n->tag === 'span') {
          $ret .= $this->dom->defaultSpanText;
        }
      }
    }
    return $ret;
  }

  /**
   * XML to text.
   */
  public function xmltext() {
    $ret = $this->innertext();
    $ret = str_ireplace('<![CDATA[', '', $ret);
    $ret = str_replace(']]>', '', $ret);
    return $ret;
  }

  /**
   * Makeup.
   */
  public function makeup() {
    // text, comment, unknown.
    if (isset($this->blank[HDOM_INFO_TEXT])) {
      return $this->dom->restoreNoise($this->blank[HDOM_INFO_TEXT]);
    }

    $ret = '<' . $this->tag;
    $i = -1;

    foreach ($this->attr as $key => $val) {
      ++$i;

      // Skip removed attribute.
      if ($val === NULL || $val === FALSE) {
        continue;
      }

      $ret .= $this->blank[HDOM_INFO_SPACE][$i][0];

      // No value attr: nowrap, checked selected...
      if ($val === TRUE) {
        $ret .= $key;
      }
      else {
        switch ($this->blank[HDOM_INFO_QUOTE][$i]) {
          case HDOM_QUOTE_DOUBLE: $quote = '"';

            break;

          case HDOM_QUOTE_SINGLE: $quote = '\'';

            break;

          default: $quote = '';
        }

        $ret .= $key
          . $this->blank[HDOM_INFO_SPACE][$i][1]
          . '='
          . $this->blank[HDOM_INFO_SPACE][$i][2]
          . $quote
          . $val
          . $quote;
      }
    }

    $ret = $this->dom->restoreNoise($ret);
    return $ret . $this->blank[HDOM_INFO_ENDSPACE] . '>';
  }

  /**
   * Find.
   */
  public function find($selector, $idx = NULL, $lowercase = FALSE) {
    $selectors = $this->parseSelector($selector);
    if (($count = count($selectors)) === 0) {
      return [];
    }
    $found_keys = [];

    // Find each selector.
    for ($c = 0; $c < $count; ++$c) {
      // The change on the below line was documented on the sourceforge
      // code tracker id 2788009
      // used to be: if (($levle=count($selectors[0]))===0) return array();
      if (($levle = count($selectors[$c])) === 0) {
        return [];
      }
      if (!isset($this->blank[HDOM_INFO_BEGIN])) {
        return [];
      }

      $head = [$this->blank[HDOM_INFO_BEGIN] => 1];
      // Combinator.
      $cmd = ' ';

      // Handle descendant selectors, no recursive!
      for ($l = 0; $l < $levle; ++$l) {
        $ret = [];

        foreach ($head as $k => $v) {
          $n = ($k === -1) ? $this->dom->root : $this->dom->nodes[$k];
          // PaperG - Pass this optional parameter on to the seek function.
          $n->seek($selectors[$c][$l], $ret, $cmd, $lowercase);
        }

        $head = $ret;
        // Next Combinator.
        $cmd = $selectors[$c][$l][4];
      }

      foreach ($head as $k => $v) {
        if (!isset($found_keys[$k])) {
          $found_keys[$k] = 1;
        }
      }
    }

    // Sort keys.
    ksort($found_keys);

    $found = [];
    foreach ($found_keys as $k => $v) {
      $found[] = $this->dom->nodes[$k];
    }

    // Return nth-element or array.
    if (is_null($idx)) {
      return $found;
    }
    elseif ($idx < 0) {
      $idx = count($found) + $idx;
    }
    return (isset($found[$idx])) ? $found[$idx] : NULL;
  }

  /**
   * Seek.
   */
  protected function seek($selector, &$ret, $parent_cmd, $lowercase = FALSE) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    [$tag, $id, $class, $attributes] = $selector;
    $nodes = [];

    // Descendant Combinator.
    if ($parent_cmd === ' ') {
      // Find parent closing tag if the current element doesn't have a closing
      // tag (i.e. void element)
      $end = (!empty($this->blank[HDOM_INFO_END])) ? $this->blank[HDOM_INFO_END] : 0;
      if ($end == 0) {
        $parent = $this->parent;
        while (!isset($parent->blank[HDOM_INFO_END]) && $parent !== NULL) {
          $end -= 1;
          $parent = $parent->parent;
        }
        $end += $parent->blank[HDOM_INFO_END];
      }

      // Get list of target nodes.
      $nodes_start = $this->blank[HDOM_INFO_BEGIN] + 1;
      $nodes_count = $end - $nodes_start;
      $nodes = array_slice($this->dom->nodes, $nodes_start, $nodes_count, TRUE);
    }
    // Child Combinator.
    elseif ($parent_cmd === '>') {
      $nodes = $this->children;
    }
    elseif ($parent_cmd === '+'
      && $this->parent
      // Next-Sibling Combinator.
      && in_array($this, $this->parent->children)) {
      $index = array_search($this, $this->parent->children, TRUE) + 1;
      if ($index < count($this->parent->children)) {
        $nodes[] = $this->parent->children[$index];
      }
    }
    elseif ($parent_cmd === '~'
      && $this->parent
      // Subsequent Sibling Combinator.
      && in_array($this, $this->parent->children)) {
      $index = array_search($this, $this->parent->children, TRUE);
      $nodes = array_slice($this->parent->children, $index);
    }

    // Go throgh each element starting at this element until the end tag
    // Note: If this element is a void tag, any previous void element is
    // skipped.
    foreach ($nodes as $node) {
      $pass = TRUE;

      // Skip root nodes.
      if (!$node->parent) {
        $pass = FALSE;
      }

      // Handle 'text' selector.
      if ($pass && $tag === 'text' && $node->tag === 'text') {
        $ret[array_search($node, $this->dom->nodes, TRUE)] = 1;
        unset($node);
        continue;
      }

      // Skip if node isn't a child node (i.e. text nodes)
      if ($pass && !in_array($node, $node->parent->children, TRUE)) {
        $pass = FALSE;
      }

      // Skip if tag doesn't match.
      if ($pass && $tag !== '' && $tag !== $node->tag && $tag !== '*') {
        $pass = FALSE;
      }

      // Skip if ID doesn't exist.
      if ($pass && $id !== '' && !isset($node->attr['id'])) {
        $pass = FALSE;
      }

      // Check if ID matches.
      if ($pass && $id !== '' && isset($node->attr['id'])) {
        // Note: Only consider the first ID (as browsers do)
        $node_id = explode(' ', trim($node->attr['id']))[0];

        if ($id !== $node_id) {
          $pass = FALSE;
        }
      }

      // Check if all class(es) exist.
      if ($pass && $class !== '' && is_array($class) && !empty($class)) {
        if (isset($node->attr['class'])) {
          $node_classes = explode(' ', $node->attr['class']);

          if ($lowercase) {
            $node_classes = array_map('strtolower', $node_classes);
          }

          foreach ($class as $c) {
            if (!in_array($c, $node_classes)) {
              $pass = FALSE;
              break;
            }
          }
        }
        else {
          $pass = FALSE;
        }
      }

      // Check attributes.
      if ($pass
        && $attributes !== ''
        && is_array($attributes)
        && !empty($attributes)) {
        foreach ($attributes as $a) {
          [
            $att_name,
            $att_expr,
            $att_val,
            $att_inv,
            $att_case_sensitivity,
          ] = $a;
          if (is_numeric($att_name)
            && $att_expr === ''
            && $att_val === '') {
            $count = 0;

            // Find index of current element in parent.
            foreach ($node->parent->children as $c) {
              if ($c->tag === $node->tag) {
                ++$count;
              }
              if ($c === $node) {
                break;
              }
            }

            // If this is the correct node, continue with next
            // attribute.
            if ($count === (int) $att_name) {
              continue;
            }
          }

          // Check attribute availability.
          // Attribute should NOT be set.
          if ($att_inv) {
            if (isset($node->attr[$att_name])) {
              $pass = FALSE;
              break;
            }
            // Attribute should be set.
          }
          else {
            // @todo "plaintext" is not a valid CSS selector!
            if ($att_name !== 'plaintext'
              && !isset($node->attr[$att_name])) {
              $pass = FALSE;
              break;
            }
          }

          // Continue with next attribute if expression isn't defined.
          if ($att_expr === '') {
            continue;
          }

          // If they have told us that this is a "plaintext"
          // search then we want the plaintext of the node - right?
          // @todo "plaintext" is not a valid CSS selector!
          if ($att_name === 'plaintext') {
            $nodeKeyValue = $node->text();
          }
          else {
            $nodeKeyValue = $node->attr[$att_name];
          }

          if (is_object($_debug_object)) {
            $_debug_object->debug_log(2,
              'testing node: '
              . $node->tag
              . ' for attribute: '
              . $att_name
              . $att_expr
              . $att_val
              . ' where nodes value is: '
              . $nodeKeyValue
            );
          }

          // If lowercase is set, do a case insensitive test of
          // the value of the selector.
          if ($lowercase) {
            $check = $this->match(
              $att_expr,
              strtolower($att_val),
              strtolower($nodeKeyValue),
              $att_case_sensitivity
            );
          }
          else {
            $check = $this->match(
              $att_expr,
              $att_val,
              $nodeKeyValue,
              $att_case_sensitivity
            );
          }

          if (is_object($_debug_object)) {
            $_debug_object->debug_log(2,
              'after match: '
              . ($check ? 'true' : 'false')
            );
          }

          if (!$check) {
            $pass = FALSE;
            break;
          }
        }
      }

      // Found a match. Add to list and clear node.
      if ($pass) {
        $ret[$node->blank[HDOM_INFO_BEGIN]] = 1;
      }
      unset($node);
    }
    // It's passed by reference so this is actually what this function returns.
    if (is_object($_debug_object)) {
      $_debug_object->debug_log(1, 'EXIT - ret: ', $ret);
    }
  }

  /**
   * Match.
   */
  protected function match($exp, $pattern, $value, $case_sensitivity) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    if ($case_sensitivity === 'i') {
      $pattern = strtolower($pattern);
      $value = strtolower($value);
    }

    switch ($exp) {
      case '=':
        return ($value === $pattern);

      case '!=':
        return ($value !== $pattern);

      case '^=':
        return preg_match('/^' . preg_quote($pattern, '/') . '/', $value);

      case '$=':
        return preg_match('/' . preg_quote($pattern, '/') . '$/', $value);

      case '*=':
        return preg_match('/' . preg_quote($pattern, '/') . '/', $value);

      case '|=':
        return strpos($value, $pattern) === 0;

      case '~=':
        return in_array($pattern, explode(' ', trim($value)), TRUE);
    }
    return FALSE;
  }

  /**
   * Parse Selector.
   */
  protected function parseSelector($selector_string) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }
    $pattern = "/([\w:\*-]*)(?:\#([\w-]+))?(?:|\.([\w\.-]+))?((?:\[@?(?:!?[\w:-]+)(?:(?:[!*^$|~]?=)[\"']?(?:.*?)[\"']?)?(?:\s*?(?:[iIsS])?)?\])+)?([\/, >+~]+)/is";

    preg_match_all(
      $pattern,
      // Add final ' ' as pseudo separator.
      trim($selector_string) . ' ',
      $matches,
      PREG_SET_ORDER
    );

    if (is_object($_debug_object)) {
      $_debug_object->debug_log(2, 'Matches Array: ', $matches);
    }

    $selectors = [];
    $result = [];

    foreach ($matches as $m) {
      $m[0] = trim($m[0]);

      // Skip NoOps.
      if ($m[0] === '' || $m[0] === '/' || $m[0] === '//') {
        continue;
      }

      // Convert to lowercase.
      if ($this->dom->lowercase) {
        $m[1] = strtolower($m[1]);
      }

      // Extract classes.
      if ($m[3] !== '') {
        $m[3] = explode('.', $m[3]);
      }

      /* Extract attributes (pattern based on the pattern above!)

       * [0] - full match
       * [1] - attribute name
       * [2] - attribute expression
       * [3] - attribute value
       * [4] - case sensitivity
       *
       * Note: Attributes can be negated with a "!" prefix to their name
       */
      if ($m[4] !== '') {
        preg_match_all(
          "/\[@?(!?[\w:-]+)(?:([!*^$|~]?=)[\"']?(.*?)[\"']?)?(?:\s+?([iIsS])?)?\]/is",
          trim($m[4]),
          $attributes,
          PREG_SET_ORDER
        );

        // Replace element by array.
        $m[4] = [];

        foreach ($attributes as $att) {
          // Skip empty matches.
          if (trim($att[0]) === '') {
            continue;
          }

          $inverted = (isset($att[1][0]) && $att[1][0] === '!');
          $m[4][] = [
            // Name.
            $inverted ? substr($att[1], 1) : $att[1],
            // Expression.
            (isset($att[2])) ? $att[2] : '',
            // Value.
            (isset($att[3])) ? $att[3] : '',
            // Inverted Flag.
            $inverted,
            // Case-Sensitivity.
            (isset($att[4])) ? strtolower($att[4]) : '',
          ];
        }
      }

      // Sanitize Separator.
      // Descendant Separator.
      if ($m[5] !== '' && trim($m[5]) === '') {
        $m[5] = ' ';
      }
      // Other Separator.
      else {
        $m[5] = trim($m[5]);
      }

      // Clear Separator if it's a Selector List.
      if ($is_list = ($m[5] === ',')) {
        $m[5] = '';
      }

      // Remove full match before adding to results.
      array_shift($m);
      $result[] = $m;

      // Selector List.
      if ($is_list) {
        $selectors[] = $result;
        $result = [];
      }
    }

    if (count($result) > 0) {
      $selectors[] = $result;
    }
    return $selectors;
  }

  /**
   * Get.
   */
  public function __get($name) {
    if (isset($this->attr[$name])) {
      return $this->convertText($this->attr[$name]);
    }
    switch ($name) {
      case 'outertext':
        return $this->outertext();

      case 'innertext':
        return $this->innertext();

      case 'plaintext':
        return $this->text();

      case 'xmltext':
        return $this->xmltext();

      default:
        return array_key_exists($name, $this->attr);
    }
  }

  /**
   * Set.
   */
  public function __set($name, $value) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    switch ($name) {
      case 'outertext':
        return $this->blank[HDOM_INFO_OUTER] = $value;

      case 'innertext':
        if (isset($this->blank[HDOM_INFO_TEXT])) {
          return $this->blank[HDOM_INFO_TEXT] = $value;
        }
        return $this->blank[HDOM_INFO_INNER] = $value;
    }

    if (!isset($this->attr[$name])) {
      $this->blank[HDOM_INFO_SPACE][] = [' ', '', ''];
      $this->blank[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
    }

    $this->attr[$name] = $value;
  }

  /**
   * Isset.
   */
  public function __isset($name) {
    switch ($name) {
      case 'outertext':
        return TRUE;

      case 'innertext':
        return TRUE;

      case 'plaintext':
        return TRUE;
    }
    // No value attr: nowrap, checked selected...
    return (array_key_exists($name, $this->attr)) ? TRUE : isset($this->attr[$name]);
  }

  /**
   * Unset.
   */
  public function __unset($name) {
    if (isset($this->attr[$name])) {
      unset($this->attr[$name]);
    }
  }

  /**
   * Convert text.
   */
  public function convertText($text) {
    global $_debug_object;
    if (is_object($_debug_object)) {
      $_debug_object->debug_log_entry(1);
    }

    $converted_text = $text;

    $sourceCharset = '';
    $targetCharset = '';

    if ($this->dom) {
      $sourceCharset = strtoupper($this->dom->charset);
      $targetCharset = strtoupper($this->dom->targetCharset);
    }

    if (is_object($_debug_object)) {
      $_debug_object->debug_log(3,
        'source charset: '
        . $sourceCharset
        . ' target charaset: '
        . $targetCharset
      );
    }

    if (!empty($sourceCharset)
      && !empty($targetCharset)
      && (strcasecmp($sourceCharset, $targetCharset) != 0)) {
      // Check if the reported encoding could have been
      // incorrect and the text is actually already UTF-8.
      if ((strcasecmp($targetCharset, 'UTF-8') == 0)
        && ($this->isUtf8($text))) {
        $converted_text = $text;
      }
      else {
        $converted_text = iconv($sourceCharset, $targetCharset, $text);
      }
    }

    // Lets make sure that we don't have that silly
    // BOM issue with any of the utf-8 text we output.
    if ($targetCharset === 'UTF-8') {
      if (substr($converted_text, 0, 3) === "\xef\xbb\xbf") {
        $converted_text = substr($converted_text, 3);
      }

      if (substr($converted_text, -3) === "\xef\xbb\xbf") {
        $converted_text = substr($converted_text, 0, -3);
      }
    }

    return $converted_text;
  }

  /**
   * Check UTF8.
   */
  public static function isUtf8($str) {
    $c = 0;
    $b = 0;
    $bits = 0;
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
      $c = ord($str[$i]);
      if ($c > 128) {
        if (($c >= 254)) {
          return FALSE;
        }
        elseif ($c >= 252) {
          $bits = 6;
        }
        elseif ($c >= 248) {
          $bits = 5;
        }
        elseif ($c >= 240) {
          $bits = 4;
        }
        elseif ($c >= 224) {
          $bits = 3;
        }
        elseif ($c >= 192) {
          $bits = 2;
        }
        else {
          return FALSE;
        }
        if (($i + $bits) > $len) {
          return FALSE;
        }
        while ($bits > 1) {
          $i++;
          $b = ord($str[$i]);
          if ($b < 128 || $b > 191) {
            return FALSE;
          }
          $bits--;
        }
      }
    }
    return TRUE;
  }

  /**
   * Get display size.
   */
  public function getDisplaySize() {
    $width = -1;
    $height = -1;

    if ($this->tag !== 'img') {
      return FALSE;
    }

    // See if there is aheight or width attribute in the tag itself.
    if (isset($this->attr['width'])) {
      $width = $this->attr['width'];
    }

    if (isset($this->attr['height'])) {
      $height = $this->attr['height'];
    }

    // Now look for an inline style.
    if (isset($this->attr['style'])) {
      // Thanks to user gnarf from stackoverflow for this regular expression.
      $attributes = [];

      preg_match_all(
        '/([\w-]+)\s*:\s*([^;]+)\s*;?/',
        $this->attr['style'],
        $matches,
        PREG_SET_ORDER
      );

      foreach ($matches as $match) {
        $attributes[$match[1]] = $match[2];
      }

      // If there is a width in the style attributes:
      if (isset($attributes['width']) && $width == -1) {
        // Check that the last two characters are px (pixels)
        if (strtolower(substr($attributes['width'], -2)) === 'px') {
          $proposed_width = substr($attributes['width'], 0, -2);
          // Now make sure that it's an integer and not something stupid.
          if (filter_var($proposed_width, FILTER_VALIDATE_INT)) {
            $width = $proposed_width;
          }
        }
      }

      // If there is a width in the style attributes:
      if (isset($attributes['height']) && $height == -1) {
        // Check that the last two characters are px (pixels)
        if (strtolower(substr($attributes['height'], -2)) == 'px') {
          $proposed_height = substr($attributes['height'], 0, -2);
          // Now make sure that it's an integer and not something stupid.
          if (filter_var($proposed_height, FILTER_VALIDATE_INT)) {
            $height = $proposed_height;
          }
        }
      }

    }

    // Future enhancement:
    // Look in the tag to see if there is a class or id specified that has
    // a height or width attribute to it.
    // Far future enhancement
    // Look at all the parent tags of this image to see if they specify a
    // class or id that has an img selector that specifies a height or width
    // Note that in this case, the class or id will have the img subselector
    // for it to apply to the image.
    // Ridiculously far future development
    // If the class or id is specified in a SEPARATE css file thats not on
    // the page, go get it and do what we were just doing for the ones on
    // the page.
    $result = [
      'height' => $height,
      'width' => $width,
    ];

    return $result;
  }

  /**
   * Save.
   */
  public function save($filepath = '') {
    $ret = $this->outertext();

    if ($filepath !== '') {
      file_put_contents($filepath, $ret, LOCK_EX);
    }

    return $ret;
  }

  /**
   * Add class.
   */
  public function addClass($class) {
    if (is_string($class)) {
      $class = explode(' ', $class);
    }

    if (is_array($class)) {
      foreach ($class as $c) {
        if (isset($this->class)) {
          if ($this->hasClass($c)) {
            continue;
          }
          else {
            $this->class .= ' ' . $c;
          }
        }
        else {
          $this->class = $c;
        }
      }
    }
    else {
      if (is_object($_debug_object)) {
        $_debug_object->debug_log(2, 'Invalid type: ', gettype($class));
      }
    }
  }

  /**
   * Has class.
   */
  public function hasClass($class) {
    if (is_string($class)) {
      if (isset($this->class)) {
        return in_array($class, explode(' ', $this->class), TRUE);
      }
    }
    else {
      if (is_object($_debug_object)) {
        $_debug_object->debug_log(2, 'Invalid type: ', gettype($class));
      }
    }

    return FALSE;
  }

  /**
   * Remove class.
   */
  public function removeClass($class = NULL) {
    if (!isset($this->class)) {
      return;
    }

    if (is_null($class)) {
      $this->removeAttribute('class');
      return;
    }

    if (is_string($class)) {
      $class = explode(' ', $class);
    }

    if (is_array($class)) {
      $class = array_diff(explode(' ', $this->class), $class);
      if (empty($class)) {
        $this->removeAttribute('class');
      }
      else {
        $this->class = implode(' ', $class);
      }
    }
  }

  /**
   * Get all attribute.
   */
  public function getAllAttributes() {
    return $this->attr;
  }

  /**
   * Get attribute.
   */
  public function getAttribute($name) {
    return $this->__get($name);
  }

  /**
   * Set attribute.
   */
  public function setAttribute($name, $value) {
    $this->__set($name, $value);
  }

  /**
   * Has attribute.
   */
  public function hasAttribute($name) {
    return $this->__isset($name);
  }

  /**
   * Remove attribute.
   */
  public function removeAttribute($name) {
    $this->__set($name, NULL);
  }

  /**
   * Remove.
   */
  public function remove() {
    if ($this->parent) {
      $this->parent->removeChild($this);
    }
  }

  /**
   * Remove child.
   */
  public function removeChild($node) {
    $nidx = array_search($node, $this->nodes, TRUE);
    $cidx = array_search($node, $this->children, TRUE);
    $didx = array_search($node, $this->dom->nodes, TRUE);

    if ($nidx !== FALSE && $cidx !== FALSE && $didx !== FALSE) {

      foreach ($node->children as $child) {
        $node->removeChild($child);
      }

      foreach ($node->nodes as $entity) {
        $enidx = array_search($entity, $node->nodes, TRUE);
        $edidx = array_search($entity, $node->dom->nodes, TRUE);

        if ($enidx !== FALSE && $edidx !== FALSE) {
          unset($node->nodes[$enidx]);
          unset($node->dom->nodes[$edidx]);
        }
      }

      unset($this->nodes[$nidx]);
      unset($this->children[$cidx]);
      unset($this->dom->nodes[$didx]);

      $node->clear();

    }
  }

  /**
   * Get element by  Id.
   */
  public function getElementById($id) {
    return $this->find("#$id", 0);
  }

  /**
   * Get elements by  Id.
   */
  public function getElementsById($id, $idx = NULL) {
    return $this->find("#$id", $idx);
  }

  /**
   * Get element by tag name.
   */
  public function getElementByTagName($name) {
    return $this->find($name, 0);
  }

  /**
   * Get elements by tag name.
   */
  public function getElementsByTagName($name, $idx = NULL) {
    return $this->find($name, $idx);
  }

  /**
   * Get parent node.
   */
  public function parentNode() {
    return $this->parent();
  }

  /**
   * Get child node.
   */
  public function childNodes($idx = -1) {
    return $this->children($idx);
  }

  /**
   * Get first child.
   */
  public function firstChild() {
    return $this->firstChilds();
  }

  /**
   * Get last child.
   */
  public function lastChild() {
    return $this->lastChilds();
  }

  /**
   * Get next sibling.
   */
  public function nextSibling() {
    return $this->nextSiblings();
  }

  /**
   * Get previous sibling.
   */
  public function previousSibling() {
    return $this->prevSibling();
  }

  /**
   * Has child node.
   */
  public function hasChildNodes() {
    return $this->hasChild();
  }

  /**
   * Get node name.
   */
  public function nodeName() {
    return $this->tag;
  }

  /**
   * Append Child.
   */
  public function appendChild($node) {
    $node->parent($this);
    return $node;
  }

}
