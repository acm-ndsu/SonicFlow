<?php

// +----------------------------------------------------------------------+
// | PHP version 5.1.4                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2003-2006.                  |
// | Share and enjoy!                                                     |
// +----------------------------------------------------------------------+
// | Updates and other scripts by Allan Hansen here:                      |
// |     http://www.artemis.dk/php/                                       |
// +----------------------------------------------------------------------+
// | abstraction.php                                                      |
// | Abstract PHP classes to generate and output HTML/XML.                |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ah@artemis.dk>                                |
// +----------------------------------------------------------------------+
//
// $Id: abstraction.php,v 1.3 2007/01/08 09:41:11 ah Exp $





/**
* HTML/XML Generator.
*
* @version      1
*/

class xml_gen
{

    /**
    * Generate XML for plain link.
    *
    * If $uri is false a grey text will be displayed instead.
    *
    * @param        string        uri           URI to open
    * @param        string        text          Text or HTML used for linking
    * @param        string        attr          Additional HTML attributes, e.g. "target=main width='100%'".
    */

    public static function a($uri, $text, $attr = false)
    {
        if (!$uri) {
            return "<span style=\"color: #999999\" $attr><font color=\"#999999\">$text</font></span>";
        }

        // Replace & with &amp; in uri
        $uri = str_replace('&', '&amp;', $uri);

        return "<a href=\"$uri\" $attr>$text</a>";
    }





    /**
    * Generate XML code for image.
    *
    * Automatically locates file- looks for it in any ../ (max 20 levels)
    * Can extract width and height automatically.
    * Border defaults to 0. Can be overridden with $attr
    *
    * @param        string        filename       Relative filename of picture or uri.
    * @param        string        width          Forced width of picture.
    * @param        string        height         Forced height of picture.
    * @param        string        attr           Additional HTML attributes, e.g. "target=main width='100%'".
    * @return       string                       Generated XML.
    */

    public static function img($filename, $attr = false, $width = false, $height = false)
    {
        // If filename is a local file, locate it somewhere in (../)*
        if (!strstr($filename, "http://") && !strstr($filename, "?")) {

            $name = preg_replace("/^\//", "", $filename);

            $i= 21;
            while (--$i && !file_exists($name)) {
                $name = '../'.$name;
            }

            if (!file_exists($name)) {
                return false;
            }

            $fn_size = $name;

            // Replace relative filename with correct path
            if (@$filename[0] != "/") {
            	$filename =  $name;
            }
        }

        // Get height and width
        if (!empty($fn_size) && (!$height || !$width)) {
            $sz = GetImageSize($fn_size);
            if (!$width) {
                $width= $sz[0];
            }
            if (!$height) {
                $height= $sz[1];
            }
        }

        // Add border code?
        $border= (stristr(" $attr", " border=")) ? '' : "border='0'";

        // Add alt code?
        $alt= (stristr(" $attr", " alt=")) ? '' : 'alt=""';

        // Replace & with &amp; in filename
        $filename = str_replace('&', '&amp;', $filename);

        // Output
        $result = "<img src=\"$filename\"";
        if ($width) {
            $result .= " width=\"$width\"";
        }
        if ($height) {
            $result .= " height=\"$height\"";
        }
        $result .= " $attr $border $alt />";
        return $result;
    }




    /**
    * Generate XML for pictorial link with image swapping.
    *
    * If uri is null, plain picture is displayed
    * Width and height of images are auto detected.
    * If using image swapping, images are preloaded with javascript.
    *
    * @param        string        uri              URI to open
    * @param        string        filename         Filename for image to display.
    * @param        string        attr             Additional HTML attributes, e.g. "target=main width='100%'"
    * @param        string        m_over           Filename for image to display when mouse is over image.
    *                                              Can contain onmouse* -- will merge with our code (our code
    *                                              will be executed first!). Except for onmouse* $attr is only
    *                                              added to <img tag. Cannot contain onmouse* code. Refer to $onmouse_merge
    * @param        array         otherswap        Swap other image: array ($name, $normal, $m_over).
    * @param        array         onmouse_merge    Addition onmouseover/out code to merge with our:
    *                                                array ($m_name_merge, $m_out_merge).
    */

    public static function a_img($uri, $filename, $attr = false, $m_over = false, $otherswap = false, $onmouse_merge = false)
    {
        // $uri is false, just output picture
        if (!$uri) {
            return xml_gen::img($filename);
        }

        // Number of times xml_gen::a_img() has been called with $m_over.
        static $called;

        // Init
        $swap_out  = false;
        $swap_over = false;

        // Generate onmouse* code
        if ($m_over || $otherswap) {

            // Increment called var
            $called++;

            // Has mouse over?
            if ($m_over) {
                $swap_out  = "swp('spluf$called','spl_f$called'); ";
                $swap_over = "swp('spluf$called','splof$called'); ";
            }

                // Otherswap?
            if ($otherswap) {
                list($os_name, $os_normal, $os_m_over)= $otherswap;
                if (!$os_name  ||  !$os_normal  ||  !$os_m_over) {
                    die("$otherswap requires array with three string.");
                }
                $swap_out  .= "swp('$os_name','spl_q$called'); ";
                $swap_over .= "swp('$os_name','sploq$called'); ";
            }
        }

        // Onmouse* merge`?
        if ($onmouse_merge) {
            list($merge_over, $merge_out)= $onmouse_merge;
            if (!$merge_over || !$merge_out) {
                die("$onmouse_merge requires array with two strings.");
            }
            $swap_out  .= $merge_out;
            $swap_over .= $merge_over;
        }

        // Set name only if swapping
        $name = ($m_over || $otherswap) ? "name='spluf$called'" : "";

        // Output link and picture
        $result = xml_gen::a($uri, xml_gen::img($filename, "$name $attr"), "onclick='this.blur()' onmouseout=\"$swap_out\" onmouseover=\"$swap_over\"");

        // If no m_over, return result
        if (!$m_over  &&  !$otherswap) {
            return $result;
        }

        // Else preload images
        $result .= "<script type='text/javascript'>\n<!--\n\n";

        // On first a_img() call, we insert the swp() javascript function.
        if ($called == 1) {
            $result .= "function swp(id,name) { if (document.images) document.images[id].src = eval(name+'.src'); }\n\n";
        }

        // Insert preload code.
        if ($m_over)  {
            $result .= "if (document.images) {
                  spl_f$called = new Image; spl_f$called.src = '$filename';
                  splof$called = new Image; splof$called.src = '$m_over';
              }";
        }

        // Insert preload code - otherswap
        if ($otherswap) {
            $result .= "if (document.images) {
                      spl_q$called = new Image; spl_q$called.src = '$os_normal';
                      sploq$called = new Image; sploq$called.src = '$os_m_over';
                  }";
        }


        return $result . "\n\n// -->\n</script>";
    }





    /**
    * Generate XML for pictorial link with image swapping -or- Simple picture -depending- on $condition.
    *
    * @param        bool        condition      Show Picture or xml_gen::a_img?
    * @param        string      selected       Filename for image to display if $condition == true. - use m_over if set to null
    * @param        string      uri            URI to open
    * @param        string      filename       Filename for image to display if $condition == false.
    * @param        string      m_over         Filename for image to display when mouse is over image.
    * @param        string      attr           Additional HTML attributes, e.g. "target=main width='100%'".
    *                                          Except for onmouse* $attr is only added to <img tag.
    * @param        string[]    otherswap      Swap other image: array ($name, $normal, $m_over).
    * @see                                     xml_gen::a_img
    */

    public static function a_img_cond($condition, $selected=null, $uri, $filename, $m_over = false, $attr = false, $otherswap = false)
    {
        if ($condition) {
            if (is_null($selected)) {
                return xml_gen::a_img($uri, $m_over, $attr, $m_over, $otherswap);
            }
            else {
                return xml_gen::a_img($uri, $selected, $attr, $m_over, $otherswap);
            }
        }
        else {
            return xml_gen::a_img($uri, $filename, $attr, $m_over, $otherswap);
        }
    }




   /**
   * Generate XML code for flash object.
   */

   function flash($filename, $width = false, $height = false, $attr = null, $bgcolor = '#ffffff', $requied_flash_version = 6, $use_swfobject = true, $swfobject_name = "mymovie")
   {
       // If filename is a local file, locate it somewhere in (../)*
       if (!strstr($filename, "http://") && !strstr($filename, "?")) {

           $name = preg_replace("/^\//", "", $filename);

           $i = 21;
           while (--$i && !file_exists($name)) {
               $name = '../'.$name;
           }

           if (!file_exists($name)) {
               return false;
           }

           $fn_size = $name;

           // Replace relative filename with correct path
           if (@$filename[0] != '/') {
               $filename =  $name;
           }
       }

       // Get height and width
       if (!empty($fn_size) && (!$height || !$width)) {
           $sz = GetImageSize($fn_size);
           $width  = ($width  ? $width  : $sz[0]);
           $height = ($height ? $height : $sz[1]);
       }

		if ($use_swfobject) {
           $div_name = 'flashcontent_'.strtr($filename, '/.=?%;', '______');

           $flashObjectString  = '<div width="'.$width.'" height="'.$height.'" id="'.$div_name.'"><table cellpadding="5" cellspacing="0" border="0" width="'.$width.'" height="'.$height.'"></tr><td bgcolor="'.$bgcolor.'"><b>Requires flash version '.$requied_flash_version.'</b></td></tr></table></div>';
           $flashObjectString .= '<script type="text/javascript">';
           $flashObjectString .= '<!--';
           $flashObjectString .= 'var so = new SWFObject("'.$filename.'", "'.$swfobject_name.'", "'.$width.'", "'.$height.'", "'.$requied_flash_version.'", "'.$bgcolor.'");';
           $flashObjectString .= 'so.addParam("quality", "high");';
           $flashObjectString .= 'so.write("'.$div_name.'");';
           $flashObjectString .= '// -->';
           $flashObjectString .= '</script>';
		} else {
       		$flashObjectString  = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=$requied_flash_version,0,0,0" width="'.$width.'" height="'.$height.'" '.$attr.'>';
			$flashObjectString .= '<param name="movie" value="'.$filename.'">';
			$flashObjectString .= '<param name="quality" value="high">';
			$flashObjectString .= '<param name="bgcolor" value="'.$bgcolor.'">';
			$flashObjectString .= '<embed src="'.$filename.'" quality="high" bgcolor="'.$bgcolor.'" width="'.$width.'" height="'.$height.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>';
			$flashObjectString .= '</object>';
		}
		return $flashObjectString;
   }




    /**
    * Generate XML for <span>.
    */

    public static function span($content, $attr = null)
    {
        return "<span $attr>$content</span>";
    }



    /**
    * Generate XML for <div>.
    */

    public static function div($content, $attr = null)
    {
        return "<div $attr>$content</div>";
    }



    /**
    * Generate XML for bold text.
    */

    public static function b($text)
    {
        return "<b>$text</b>";
    }




    /**
    * Generate XML for italic text.
    */

    public static function i($text)
    {
        return "<i>$text</i>";
    }




    /**
    * Generate XML for pre formatted text.
    */

    public static function pre($text, $attr = null)
    {
        return "<pre $attr>$text</pre>\n";
    }




    /**
    * Generate XML for paragraph.
    */

    public static function p($text, $attr = null)
    {
        if (is_array($text)) {
            $text = implode("<br>", $text);
        }

        return "<p $attr>$text</p>\n";
    }




    /**
    * Generate XML for error paragraph.
    */

    public static function p_err($text)
    {
        if (is_array($text)) {
            $text = implode("<br>", $text);
        }

        return xml_gen::p($text, "class='error'");
    }




    /**
    * Generate XML for text as heading 1.
    */

    public static function h1($text, $attr='')
    {
        return "<h1 $attr>$text</h1>";
    }




    /**
    * Generate XML for text as heading 2.
    */

    public static function h2($text, $attr='')
    {
        return "<h2 $attr>$text</h2>";
    }




    /**
    * Generate XML for text as heading 3.
    */

    public static function h3($text, $attr='')
    {
        return "<h3 $attr>$text</h3>";
    }




    /**
    * Generate XML for text as heading 4.
    */

    public static function h4($text, $attr='')
    {
        return "<h4 $attr>$text</h4>";
    }




     /**
    * Generate XML for spaces.
    */

    public static function space($n=1)
    {
        return str_repeat("&nbsp;", $n);
    }




    /**
    * Generate XML for linefeeds.
    */

    public static function br($n = 1, $abs = null)
    {
        if ($abs) {
            $abs= " clear=all";
        }
        return str_repeat("<br$abs />\n", $n);
    }




    /**
    * Generate XML for hr tag.
    */

    public static function hr($attr = null)
    {
        return "<hr $attr />";
    }




    /**
    * Generate XML for unordered list.
    */

    public static function ul($elements, $attr = null)
    {
        return "<ul $attr>$elements</ul>\n";
    }




    /**
    * Generate XML for list index.
    */

    public static function li($text, $attr = null)
    {
        return "<li $attr />$text\n";
    }




    /**
    * Generate XML for iframe
    *
    * Defaults to no scrolling and no frameborder.
    * Automatic height and width possible for local uris only.
    *
    * @param    string      src         Iframe source (src)
    * @param    mixed       width       Iframe width  - set to 'auto' to automatically adjust width.
    * @param    mixed       height      Iframe height - set to 'auto' to automatically adjust height
    * @param    string      attr        Additional HTML attributes, e.g. "target=main width='100%'".
    */

    public static function iframe($src, $width=null, $height=null, $attr=null)
    {
        if (!strstr($attr, 'frameborder')) {
            $attr .= " frameborder='0'";
        }
        
        if (!strstr($attr, 'scrolling')) {
            $attr .= " scrolling='no'";
        }
        
        if ($width == 'auto'  ||  $height == 'auto') {
            
            $on_load = '';
            
            if ($width == 'auto') {
                $on_load .= 'this.width=this.contentWindow  ? this.contentWindow.document.body.scrollWidth  : this.document.body.scrollWidth;';
            }
            
            if ($height == 'auto') {
                $on_load .= 'this.height=this.contentWindow ? this.contentWindow.document.body.scrollHeight : this.document.body.scrollHeight;';
            }
            
            $attr .= "onLoad='$on_load'";
        }

        $result = "<iframe src='$src'";

        if ($width) {
            $result .= " width='$width'";
        }

        if ($height) {
            $result .= " height='$height'";
        }
        
    
        return "$result $attr>An iframe capable browser is required to view this web site</iframe>";
    }




    /**
    * Generate XML for invisible table with desired width and height.
    */

    public static function spacer($width, $height)
    {
        return "<table cellpadding='0' cellspacing='0' border='0' width='$width' style='height:${height}px'><tr><td style='Font: 1px Arial'>&nbsp;</td></tr></table>";
    }




    /**
    * Generate js onclick for confirmations for <a
    */

    public static function js_confirm($str)
    {
        $str = xml_gen::js_string($str);

        return "onclick='return confirm(\"$str\");'";
    }




    /**
    * Generate js onclick for confirmations for <input type=button
    */

    public static function button_js_confirm_href($str, $url)
    {
        $str = xml_gen::js_string($str);

        return "onclick='if (confirm(\"$str\")) location.href=\"$url\";'";
    }



    /**
    * Prepare string for javascript
    */

    public static function js_string($str)
    {
        $str = str_replace('"', '\"',  $str);
        $str = str_replace("'", '"+String.fromCharCode(39)+"', $str);

        return $str;
    }

}






//////////////////////////////////////////////////////////////////////////////
// Tables ////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


/**
* HTML Table Generator.
*
* @version        2
*/

class table
{
    protected $curr_col;            // Current column we are in
    protected $curr_row;            // Current row we are in
    protected $rowspan;             // Array to handle rowspan
    protected $closed;              // Is current cell closed?
    protected $columns;             // Number of column in table
    protected $aligns;              // Alignments from constructor
    protected $widths;              // widths from constructor
    protected $classes;             // classes from constructor
    protected $header;              // Does table has header row
    protected $set_widths;          // Set only widths on first row with no colspan
    protected $next_row_attr = '';  // Set id= on next <tr>




    /**
    * Constructor - define table layout here.
    *
    * @param    int      colums   Number of colums in table
    * @param    string   attr     Additional HTML attributes. cellpadding, cellspacing and border all defaults to 0.
    * @param    string   classes  classes (set on <tr>) to alternate. "header;alternate" or ";alternate".
    * @param    string   aligns   Alignments of columns. List seperated by ;.  I.e. "left;right;center;left".
    * @param    string   widths   widths of columns. List seperated by ;. I.e. "100;20%;20".
    *                             Alternate is ; seperated too, I.e. "header;odd;even".
    */

    public function __construct($columns, $attr=false, $classes=false, $aligns=false, $widths=false)
    {
        echo "\n\n".'<table';

        // Default cellpadding: 0
        if (!stristr(' '.$attr, ' cellpadding=')) {
            echo ' cellpadding="0"';
        }

        // Default cellspacing: 0
        if (!stristr(' '.$attr, ' cellspacing=')) {
            echo ' cellspacing="0"';
        }

        // Default border: 0
        if (!stristr(' '.$attr, ' border=')) {
            echo ' border="0"';
        }

        echo ' '.$attr.'>';

        $this->closed     = true;     // Table is closed
        $this->curr_col   = -1;       // We are at column -1 (before <tr>)
        $this->curr_row   = 0;        // We are in row 0
        $this->columns    = $columns;
        $this->header     = $classes && ($classes[0] != ';');
        $this->aligns     = explode(';', $aligns);
        $this->widths     = explode(';', $widths);
        $this->classes    = explode(';', $classes);
        $this->set_widths = 1;
        $this->rowspan    = array();
    }




    /**
    * Destructor - finalise table.
    */

    public function done()
    {
        $this->end_row();
        echo "</table>\n\n";
    }




    /**
    * Output data cell(s).
    *
    * @param        mixed         data        String: Data to output. Can be blank... just output after function call.
    *                                         Array:  Each element will be output in a new cell.
    * @param        string        attr        Additional HTML attributes. Set col/rowspan, override/set class, width, align...
    */

    public function data($data = '', $attr = false)
    {
        if (is_array($data)) {
            foreach ($data as $element) {
                $this->data($element);
            }
            return;
        }

        // Before <tr>?
        if ($this->curr_col == -1) {
            $this->new_row();
        }

        // After last column?
        elseif ($this->curr_col + $this->rowspan[0] >= $this->columns) {
            $this->end_row();
            $this->new_row();
        }

        // After <td>?
        if ($this->curr_col > 0) {
            echo '</td>'."\n";
            $this->closed = true;
        }

        // Begin new cell
        echo '<td';
        $this->closed= false;

        // Override width
        if (!stristr(" $attr", ' width=')  &&  !stristr(" $attr", ' colspan=')) {
            if ($this->curr_row == $this->set_widths  &&  sizeof($this->widths > $this->curr_col)  &&  !empty($this->widths[$this->curr_col])) {
                echo " width='".$this->widths[$this->curr_col]."'";
            }
        }

        // Override alignment
        if (!stristr(" $attr", ' align=')) {
            if (sizeof($this->aligns > $this->curr_col) && !empty($this->aligns[$this->curr_col])) {
                echo ' align="'.$this->aligns[$this->curr_col].'"';
            }
        }

        // Output data
        echo ' '.$attr.'>'.$data;

        // Advance internal pointer
        $this->curr_col++;

        // Colspan?
        $colspan = 1;
        if (preg_match('# colspan=(["\'0-9]+) #i', ' '.$attr.' ', $regs)) {
            $colspan = str_replace("'", '', str_replace('"', '', $regs[1]));
            if ($colspan > 1) {
                $this->curr_col += $colspan - 1;
            }
            if ($this->curr_row == $this->set_widths) {
                $this->set_widths++;
            }
        }

        // rowspan?
        if (preg_match('# rowspan=(["\'0-9]+) #i', ' '.$attr.' ', $regs)) {
            $rowspan = str_replace("'", '', str_replace('"', '', $regs[1]));
            while ($rowspan > 1) {
                array_push($this->rowspan, 0); // add a elements to our array...  we may not need it... who cares?
                @$this->rowspan[$rowspan - 1] += $colspan;
                $rowspan--;
            }
        }

    }




    /**
    * Set attributes on next <tr>.
    */

    public function row_attr($attr)
    {
        $this->next_row_attr = ' '.$attr;
    }




    /**
    * Finalise current row. Fill with empty cells if needed.
    */

    public function end_row()
    {
        if (($this->curr_col == -1)  &&  $this->closed) {
            return;
        }

        // Before last column?
        if ($this->curr_col + @$this->rowspan[0] < $this->columns) {
            $colspan = $this->columns - $this->curr_col - @$this->rowspan[0] + 1;
            $this->data('', 'colspan="'.$colspan.'"');
        }

        if (!$this->closed) {
            echo '</td>'."\n";
        }

        echo '</tr>'."\n";

        $this->curr_col = -1;
        ksort($this->rowspan);
        array_shift($this->rowspan);
    }





    /**
    * Advance to new row.
    */

    protected function new_row()
    {
        echo "\n".'<tr';

        // Override class
        if (!stristr(' '.$this->next_row_attr, ' class=')) {
            echo $this->get_class();
        }

        echo $this->next_row_attr;
        $this->next_row_attr = '';
        echo '>'."\n";
        $this->curr_row++;
        $this->curr_col = 0;
        array_push($this->rowspan, 0);
    }





    /**
    * Get class for current row.
    */

    protected function get_class()
    {
        $sz = sizeof($this->classes);
        if (!$sz) {
            return;
        }

        $class = $this->classes[0];
        if (($this->curr_row == 0) && $this->header && $class) {
            return ' class="'.$class.'"';
        }

        $offset = 1;
        if ($this->header) {
            $offset--;
        }

        $offs = (($sz > 1) ? 1 + (($offset + $this->curr_row - 1) % ($sz - 1)) : 1);
        $class = isset($this->classes[$offs]) ? $this->classes[$offs] : null;
        if ($class) {
            return ' class="'.$class.'"';
        }
    }
}





//////////////////////////////////////////////////////////////////////////////
// Forms /////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


/**
* HTML Form Generator.
*
* @version        3
*/

class form
{
    protected $elements;              // Array of elements: array ( array("fisk", "radio"), ...
    protected $last_date_type;        // YMD order of last date field
    protected $focus_element  = -1;   // Element on which to focus with javascript.
    protected $check_code;            // Javascript code for CheckFormX()
    protected $extra_code_pre;        // Extra javascript when drawing form
    protected $extra_code_post;       // Extra javascript before submit
    protected $values;                // Object or array containing default values.

    public $name;                     // Name of form.

    public $class_fields             = "field";           // Standard class for textfields, textareas
    public $class_fields_readonly    = "field_readonly";  // Standard class for readonly fields
    public $class_fields_disabled    = "field_disabled";  // Standard class for disabled fields
    public $class_selects            = "select";          // Standard class for selectboxes
    public $class_selects_readonly   = "select_readonly"; // Standard class for readonly selectboxes
    public $class_selects_disabled   = "select_disabled"; // Standard class for disabled selectboxes
    public $class_buttons            = "button";          // Standard class for buttons
    public $class_checkboxes         = "checkbox";        // Standard class for checkboxes
    public $class_radiobuttons       = "radiobutton";     // Standard class for radiobuttons
    public $class_labels             = "label";           // Standard class for labels (checkboxes and radiobuttons)
    public $class_labels_readonly    = "label_readonly";  // Standard class for "readonly" labels

    public $class_datefields         = "datefield";       // Standard class for datefield combi - do not set width
    public $class_dual_select_marked = "marked";          // Standard class for dual select - marked option

    public $option_dhtml_options   = false;               // Add options to <select> with DHTML. Faster when number of
                                                          // options exceeds some 2000-4000.



    /**
    * Constructor - define form here.
    *
    * @param    string  action      URL to POST or GET - defaults to $_SERVER["REQUEST_URI"]
    * @param    string  name        HTML name of form.
    * @param    string  attr        Additional HTML attributes, e.g. "target=main"
    */

    public function __construct($action = null, $name = null, $attr = null)
    {
        // Default
        if (is_null($action)) {
            $action = $_SERVER["REQUEST_URI"];
        }

        // Init
        $this->elements = array ();

        // Generate name if none is specified.
        if (!$name) {
            $name = "Form" . uniqid(42);
        }
        $this->name = $name;

        // Determine method - look for method='get' in $attr, set to post if not present
        if (!stristr(" $attr", " method='get'") && !stristr(" $attr", ' method="get"') && !stristr(" $attr", ' method=get')) {
            $attr = "method='post' enctype='multipart/form-data' $attr";
            $this->values = &$_POST;
        }
        else {
            $this->values = &$_GET;
        }

        // Output form header
        echo "\n\n<form action=\"$action\" name='$name' $attr onsubmit=\"return Check$name();\">\n";

        // Output hidden field to fix msie error
        $this->hidden("field_to_fix_msie_bug_on_post_special_chars", 42);
    }




    /**
    * Destructor - finalise form.
    */

    public function done()
    {
        // Output form footer
        echo "\n</form>\n\n";

        // Output javascript
        echo "<script type='text/javascript'>\n<!--\n\n";

        // Focus
        if ($this->focus_element != -1) {

            // Get element name from focus_element number
            foreach ($this->elements as $element_num => $array) {
                list($element, $type) = $array;
                if (!$this->focus_element) {
                    break;
                }
                $this->focus_element--;
            }

            // Set focus on selected or first element in form
            echo "if (!document.$this->name.elements[$element_num].disabled)\n";
            echo "    document.$this->name.elements[$element_num].focus();\n\n";
        }

        // Output Additional code
        echo $this->extra_code_pre;

        // Output CheckForm code
        echo "function Check" . $this->name . "()\n{\n";
        echo $this->check_code;
        echo "\n";
        echo $this->extra_code_post;
        echo "\n";
        echo "  return true;\n";
        echo "}\n\n\n// -->\n</script>\n\n";
    }




    /**
    * Set object or array to get default values from.
    *
    * Defaults to $_REQUEST.
    *
    * @param    mixed   from    Array or object.
    */

    public function values(&$from)
    {
        $this->values = &$from;
    }




    /**
    * Output a hidden field.
    *
    * Value comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of hidden field.
    * @param    string  value       Static value, ignore $_REQUEST[$name] and $GLOBALS[$name]
    */

    public function hidden($name, $value = null, $attr = null)
    {
        // Get value
        if (is_null($value)) {
            $value = $this->get($name);
        }

        // Value is array - make multiple hidden fields
        if (strstr($name, "[]")) {

            // no empty arrays
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $this->hidden(str_replace("[]", "[$key]", $name), $val);
                }
            }
            return;
        }

        // Convert " to HTML
        $value = str_replace('"', '&quot;', $value);

        // Output hidden field
        echo "<input type='hidden' name='$name' value=\"$value\" $attr />";

        // Store element in elements array
        array_push($this->elements, array ($name, "hidden"));
    }





    /**
    * Output a radio button.
    *
    * Checked button comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of radio group.
    * @param    string  value       Value for this radio button.
    * @param    string  label       Text label.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function radio($name, $value, $label = null, $attr = false)
    {
        // Get value from name
        $val   = $this->get($name);
        $check = $val == $value;

        // Convert $check to HTML
        $check = ($check) ? "checked" : "";

        if ($label) {
            // Render label grey if attr contains "disabled".
            if (stristr(" $attr ", " disabled ")) {
                $label = "<span style=\"color: #999999\"><font color=\"#999999\">$label</font></span>";
            }
            elseif ($this->class_labels) {
                $label = "<span class='$this->class_labels'>$label</span>";
            }
        }

        // Get box class
        $class = empty($this->class_radiobuttons) ? "" : "class='$this->class_radiobuttons'";

        // Output radio button
        echo "\n<input type='radio' name='$name' value=\"$value\" $check $class $attr />$label";

        // Store element in elements array
        array_push($this->elements, array ($name, "radio"));
    }





    /**
    * Output a checkbox
    *
    * Checked comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of check box.
    * @param    string  label       Text label.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function checkbox($name, $label = null, $attr = false)
    {
        $value = $this->get($name);
        $value = !empty($value);

        // Convert $check and value to HTML
        $check = $value ? "checked" : "";

        // Render label grey if attr contains "disabled".
        if ($label) {
            if (stristr(" $attr ", " disabled ")) {
                $label = "<span style=\"color: #999999\"><font color=\"#999999\">$label</font></span>";
            }
            elseif ($this->class_labels) {
                $label = "<span class='$this->class_labels'>$label</span>";
            }
        }

        // Get box class
        $class = empty($this->class_checkboxes) ? "" : "class='$this->class_checkboxes'";

        // Output checkbox
        echo "\n<input type='checkbox' name='$name' $check $attr $class />$label";

        // Store element in elements array
        array_push($this->elements, array ($name, "checkbox"));
    }





    /**
    * Output a select box
    *
    * Selected element comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of select box.
    * @param    array   items       Associative array with items: $value => $label.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function select($name, $items, $attr = false)
    {
        $select = $this->get($name);

        // convert select to array
        if (!is_array($select)) {
            $select = array (0 => $select);
        }

        // Use class_select_disabled if no class or style is specified in attr and field readonly.
        if ($this->class_selects_disabled  &&  stristr(" $attr", " disabled")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_selects_disabled'";
        }

        // Use class_select_readonly if no class or style is specified in attr and field readonly.
        if ($this->class_selects_readonly  &&  stristr(" $attr", " readonly")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_selects_readonly'";
        }

        // Use class_selects if no class or style is specified in attr.
        if ($this->class_selects  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_selects'";
        }


        // Output select - the normal HTML way
        if (!$this->option_dhtml_options) {

            echo "<select name='$name' $attr>";

            // Output box elements
            foreach ($items as $value => $label) {
                $selected = in_array($value, $select) ? "selected='selected'" : "";
                echo "<option value=\"$value\" $selected>$label</option>";
            }

            echo "</select>";
        }


        // Output box elements - the DHTML way
        else {

            echo "<select name='$name' $attr></select>\n";

            echo "<script type='text/javascript'>\n";

            // Generate box elements
            $i = 0;
            foreach ($items as $value => $label) {

                $value = str_replace('"', '\"',  $value);
                $value = str_replace("'", '"+String.fromCharCode(39)+"', $value);

                $label = str_replace('"', '\"',  $label);
                $label = str_replace("'", '"+String.fromCharCode(39)+"', $label);

                echo "document.$this->name.${name}[$i]= new Option(\"$label\",\"$value\");\n";

                if (in_array($value, $select)) {
                    echo "document.$this->name.$name.selectedIndex = $i;\n";
                }

                $i++;
            }

            echo "\n</script>";
        }

        // Store element in elements array
        array_push($this->elements, array ($name, "select"));
    }




    /**
    * Output a submit button.
    *
    * @param    string  label            Label on button.
    * @param    string  attr             Additional HTML attributes, e.g. "id=main"
    * @param    string  $change_action   Change action to this value before submit.
    */

    public function submit($label, $attr = false, $change_action = false)
    {
        // Use class_buttons if no class or style is specified in attr.
        if ($this->class_buttons  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_buttons'";
        }

        // Change form action
        if ($change_action) {
            $change_action = "onclick=\"$this->name.action='$change_action';\"";
        }

        // Output button
        echo "<input type='submit' value=\"$label\" $attr $change_action />";
    }





    /**
    * Output a reset button.
    *
    * @param    string  label       Label on button.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function reset($label, $attr = false)
    {
        // Use class_buttons if no class or style is specified in attr.
        if ($this->class_buttons  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_buttons'";
        }

        // Output button
        echo "<input type='reset' value=\"$label\" $attr />";
    }





    /**
    * Output a button.
    *
    * @param    string  label       Label on button.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function button($label, $attr = false)
    {
        // Use class_buttons if no class or style is specified in attr.
        if ($this->class_buttons  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_buttons'";
        }

        // Output button
        echo "<input type='button' value=\"$label\" $attr />";
    }




    /**
    * Output an image submit button.
    *
    * @param    string  label            Label on button.
    * @param    string  attr             Additional HTML attributes, e.g. "id=main"
    * @param    string  $change_action   Change action to this value before submit.
    */

    public function image($image, $text, $attr = false, $change_action = false)
    {
        // Change form action
        if ($change_action) {
            $change_action = "onclick=\"$this->name.action='$change_action';\"";
        }

        // Output button
        echo "<input type='image' value='$text' src='$image' $attr $change_action />";
    }




    /**
    * Output a text field.
    *
    * Value comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of text field.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function text($name, $attr = false)
    {
        $value = $this->get($name);

        // Convert " to HTML
        $value = str_replace('"', '&quot;', $value);

        // Use class_fields_disabled if no class or style is specified in attr and field readonly.
        if ($this->class_fields_disabled  &&  stristr(" $attr", " disabled")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_disabled'";
        }

        // Use class_fields_readonly if no class or style is specified in attr and field readonly.
        if ($this->class_fields_readonly  &&  stristr(" $attr", " readonly")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_readonly'";
        }

        // Use class_fields if no class or style is specified in attr.
        if ($this->class_fields  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields'";
        }

        // Output text field
        echo "<input type='text' name='$name' value=\"$value\" $attr />";

        // Store element in elements array
        array_push($this->elements, array ($name, "text"));
    }




    /**
    * Output a password field.
    *
    * Value comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of password field.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function password($name, $attr = false)
    {
        // Get value
        $value = $this->get($name);

        // Convert " to HTML
        $value = str_replace('"', '&quot;', $value);

        // Use class_fields_disabled if no class or style is specified in attr and field readonly.
        if ($this->class_fields_disabled  &&  stristr(" $attr", " disabled")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_disabled'";
        }

        // Use class_fields_readonly if no class or style is specified in attr and field readonly.
        if ($this->class_fields_readonly  &&  stristr(" $attr", " readonly")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_readonly'";
        }

        // Use class_fields if no class or style is specified in attr.
        if ($this->class_fields  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='".$this->class_fields."'";
        }

        // Output text field
        echo "<input type='password' name='$name' value=\"$value\" $attr />";

        // Store element in elements array
        array_push($this->elements, array ($name, "password"));
    }




    /**
    * Output a text area.
    *
    * Value comes from array or object defined with values.
    * Default from $_REQUEST[$name].
    *
    * @param    string  name        Name of text area.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function textarea($name, $attr = false)
    {
        // Get value
        $value = $this->get($name);

        // Convert " to HTML
        $value = str_replace('"', '&quot;', $value);

        // Use class_fields_disabled if no class or style is specified in attr and field readonly.
        if ($this->class_fields_disabled  &&  stristr(" $attr", " disabled")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_disabled'";
        }

        // Use class_fields_readonly if no class or style is specified in attr and field readonly.
        if ($this->class_fields_readonly  &&  stristr(" $attr", " readonly")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_readonly'";
        }

        // Use class_fields if no class or style is specified in attr.
        if ($this->class_fields  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields'";
        }

        // Add required rows attribute if not specified in attr
        if (!stristr(" $attr", " rows=")) {
            $attr .= " rows='3'";
        }

        // Add required cols attribute if not specified in attr
        if (!stristr(" $attr", " cols=")) {
            $attr .= " cols='40'";
        }

        // Output text field
        echo "<textarea name='$name' $attr>$value</textarea>";

        // Store element in elements array
        array_push($this->elements, array ($name, "textarea"));
    }





    /**
    * Output a file upload field.
    *
    * @param    string  name        Name of file upload field.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function file_upload($name, $attr = false)
    {
        // Use class_fields_disabled if no class or style is specified in attr and field readonly.
        if ($this->class_fields_disabled  &&  stristr(" $attr", " disabled")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_disabled'";
        }

        // Use class_fields_readonly if no class or style is specified in attr and field readonly.
        if ($this->class_fields_readonly  &&  stristr(" $attr", " readonly")  && !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields_readonly'";
        }

        // Use class_fields if no class or style is specified in attr.
        if ($this->class_fields  &&  !stristr(" $attr", " class=")  &&  !stristr(" $attr", " style=")) {
            $attr .= " class='$this->class_fields'";
        }

        // Output text field
        echo "<input type='file' name='$name' $attr />";

        // Store element in elements array
        array_push($this->elements, array ($name, "file"));
    }




    /**
    * Output a two field dual select box for multiple choices
    *
    * @param    string  name        Name of hidden field receiving selected values.
    * @param    array   items       Associative array with items: $value => $label.
    * @param    string  attr        Additional HTML attributes for <select
    *
    * Note: After posting $_POST[$name] will contain comma separated list of selected keys in $items.
    *       Input (selected values at start) in form->values->$name can be comma separated like above or array like normal select.
    */

    public function dual_select($name, $items, $attr = false)
    {
        // Build array of selected values
        $select = $this->get($name);
        if (!is_array($select) && $select) {
            foreach (explode(',', $select) as $id) {
                $array[$id] = $id;
            }
            $select = &$array;
        }
        $selected = array ();
        foreach ($items as $value => $label) {
            if (isset($select[$value])) {
                $selected[$value] = $label;
            }
        }

        $this->hidden($name, '');
        $this->select($name.'_1', $items,    $attr . " id='dual_${name}_a' onChange='${name}_dual_select_source_onChange()'");
        $this->select($name.'_2', $selected, $attr . " id='dual_${name}_b' onClick='${name}_dual_select_dest_onClick()'");

        echo "
        <script type='text/javascript'>
        <!--

            function ${name}_dual_select_source_onChange() {

                var boxLength     = document.$this->name.${name}_2.length;
                var selectedItem  = document.$this->name.${name}_1.selectedIndex;
                var selectedText  = document.$this->name.${name}_1.options[selectedItem].text;
                var selectedValue = document.$this->name.${name}_1.options[selectedItem].value;
                var isNew = true;
                var i;
                for (i = 0; i < boxLength; i++) {
                    if (document.$this->name.${name}_2.options[i].value == selectedValue) {
                        isNew = false;
                        break;
                    }
                }
                if (isNew) {
                    newoption = new Option(selectedText, selectedValue, false, false);
                    document.$this->name.${name}_2.options[boxLength] = newoption;
                }

                document.$this->name.${name}_1.options[selectedItem].className = '$this->class_dual_select_marked';

                ${name}_dual_select_save();
            }

            function ${name}_dual_select_dest_onClick() {

                var boxLength = document.$this->name.${name}_2.length;
                var arrSelected = new Array();
                var count = 0;
                var i;
                for (i = 0; i < boxLength; i++) {
                    if (document.$this->name.${name}_2.options[i].selected) {
                        arrSelected[count] = document.$this->name.${name}_2.options[i].value;
                    }
                    count++;
                }
                for (i = 0; i < boxLength; i++) {
                    var x;
                    for (x = 0; x < arrSelected.length; x++) {
                        if (document.$this->name.${name}_2.options[i].value == arrSelected[x]) {
                            ${name}_dual_select_mark(document.$this->name.${name}_2.options[i].value, '');
                            document.$this->name.${name}_2.options[i] = null;
                        }
                    }
                    boxLength = document.$this->name.${name}_2.length;
                }
                ${name}_dual_select_save();
            }

            function ${name}_dual_select_mark(selectedValue, className) {

                var boxLength     = document.$this->name.${name}_1.length;
                var i;
                for (i = 0; i < boxLength; i++) {
                    if (document.$this->name.${name}_1.options[i].value == selectedValue) {
                        document.$this->name.${name}_1.options[i].className = className;
                        return;
                    }
                }
            }

            function ${name}_dual_select_save() {

                var strValues = '';
                var boxLength = document.$this->name.${name}_2.length;
                var count = 0;
                if (boxLength != 0) {
                    for (i = 0; i < boxLength; i++) {
                        if (count == 0) {
                            strValues = document.$this->name.${name}_2.options[i].value;
                        }
                        else {
                            strValues = strValues + ',' + document.$this->name.${name}_2.options[i].value;
                        }
                        count++;
                    }
                }
                document.$this->name.$name.value= strValues;
            }

            ${name}_dual_select_save();
        ";

        // mark selected options
        foreach ($selected as $key => $value) {
            echo "
            ${name}_dual_select_mark($key, '$this->class_dual_select_marked');
            ";
        }

        echo "
            // -->
            </script>
        ";
    }




    /**
    * Output three form fields that act as a date contruction with javascript validation.
    *
    * type:         ISO        YYYY-MM-DD
    *               UK/US/EN   MM/DD/YYYY
    *               DK/DA      DD-MM-YYYY
    *
    * validation:   NONE    no validation.
    *               NORMAL  require valid date.
    *               EMPTY   require valid date or empty.
    *
    * Define the following constants before for non-English error messages:
    *   FORM_INCORRECT_DATE
    *   FORM_INCORRECT_MONTH
    *   FORM_INCORRECT_YEAR
    *   FORM_INCORRECT_DATEMONTH
    *   FORM_MUST_SPECIFY_DATE
    *
    * @param    string  name        Name of file upload field.
    * @param    integer type        Type of field.
    * @param    integer validation  Type of validation.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function date3($name, $type = "ISO", $validate = "NORMAL", $attr = false)
    {
        // Define constants for validation
        @define("FORM_INCORRECT_DATE",      "Specified date is incorrect.");
        @define("FORM_INCORRECT_MONTH",     "Specified month is incorrect.");
        @define("FORM_INCORRECT_YEAR",      "Specified year is incorrect.");
        @define("FORM_INCORRECT_DATEMONTH", "Specified month/date is incorrect.");
        @define("FORM_MUST_SPECIFY_DATE",   "Date must be specified.");

        // Get value
        $value = $this->get($name);

        // Extract parts and save
        $this->set("${name}__yy", substr($value, 0, 4));
        $this->set("${name}__mm", substr($value, 5, 2));
        $this->set("${name}__dd", substr($value, 8, 2));

        // Output a hidden field that will contain date afterwards. Require javascript.
        $this->hidden($name, '');
        $fname = $this->name;
        $this->extra_code_post .= "if (document.$fname.${name}__yy.value) document.$fname.$name.value = ('0000' + document.$fname.${name}__yy.value).substr(document.$fname.${name}__yy.value.length, 4) + '-' + ('00' + document.$fname.${name}__mm.value).substr(document.$fname.${name}__mm.value.length, 2) + '-' + ('00' + document.$fname.${name}__dd.value).substr(document.$fname.${name}__dd.value.length, 2);";

        // Save old field class
        $class_fields = $this->class_fields;

        // Replace class_fields
        $this->class_fields = $this->class_datefields;

        // Output fields according to type
        switch ($type) {

            case 'ISO':

                // Save order for validation
                list($y, $m, $d) = array ("¤¤¤", "¤¤", "¤");

                // Output fields
                $this->text("${name}__yy", "size=4");
                echo " - ";
                $this->text("${name}__mm", "size=2");
                echo " - ";
                $this->text("{$name}__dd", "size=2");
                break;


            case 'DK':
            case 'DA':

                // Save order for validation
                list($y, $m, $d) = array ("¤", "¤¤", "¤¤¤");

                // Output fields
                $this->text("{$name}__dd", "size=2");
                echo " - ";
                $this->text("${name}__mm", "size=2");
                echo " - ";
                $this->text("${name}__yy", "size=4");
                break;


            case 'UK':
            case 'US':
            case 'EN':

                // Save order for validation
                list($y, $m, $d) = array ("¤", "¤¤¤", "¤¤");

                // Output fields
                $this->text("${name}__mm", "size=2");
                echo " / ";
                $this->text("{$name}__dd", "size=2");
                echo " / ";
                $this->text("${name}__yy", "size=4");
                break;


            default:
                die('invalid type');
        }

        // Restore old field class
        $this->class_fields = $class_fields;

        // Validate, correct date or empty
        if ($validate != "NONE") {

            // Validate in correct order
            for ($i = 3; $i >= 1; $i--) {

                if (strlen($y) == $i) {
                    $this->validate("!((!$d.value.length  &&  !$m.value.length  &  !$y.value.length)  ||  ($y.value >= 1900  &&  $y.value <= 2100))", FORM_INCORRECT_YEAR,      $y);
                }

                if (strlen($m) == $i) {
                    $this->validate("!((!$d.value.length  &&  !$m.value.length  &  !$y.value.length)  ||  ($m.value  > 0  &&   $m.value < 13))",      FORM_INCORRECT_MONTH,     $m);
                }

                if (strlen($d) == $i) {
                    $this->validate("!((!$d.value.length  &&  !$m.value.length  &  !$y.value.length)  ||  ($d.value > 0  &&  $d.value < 32))",        FORM_INCORRECT_DATE,      $d);
                    $this->validate("$d.value == 31  &&  ($m.value == 4  ||  $m.value == 6  ||  $m.value == 9  ||  $m.value == 11)",                  FORM_INCORRECT_DATEMONTH, $d);
                    $this->validate("$d.value > 29   &&   $m.value == 2",                                                                             FORM_INCORRECT_DATEMONTH, $d);
                    $this->validate("$d.value == 29  &&   $m.value == 2  &&  ($y.value % 4)  > 0",                                                    FORM_INCORRECT_DATE,      $d);
                }
            }
        }

        // Not empty
        if ($validate == "NORMAL") {
            $this->validate("!$d.value.length  &&  !$m.value.length  &&  !$y.value.length",  FORM_MUST_SPECIFY_DATE, '¤¤¤');
        }

        // Save type if we desire to validate further
        $this->last_datetime_type = array ($y, $m, $d);
    }




    /**
    * Output five form fields that act as a date + time contruction with javascript validation.
    *
    * type:         ISO        YYYY-MM-DD HH:NN
    *               UK/US/EN   MM/DD/YYYY HH:NN     (24h)
    *               DK/DA      DD-MM-YYYY HH:NN
    *
    * validation:   NONE    no validation.
    *               NORMAL  require valid date/time
    *               EMPTY   require valid date/time or empty.
    *
    * Define the following constants before for non-English error messages:
    *   FORM_INCORRECT_DATE
    *   FORM_INCORRECT_MONTH
    *   FORM_INCORRECT_YEAR
    *   FORM_INCORRECT_DATEMONTH
    *   FORM_MUST_SPECIFY_DATE
    *   FORM_INCORRECT_HOUR
    *   FORM_INCORRECT_MINUTE
    *
    * @param    string  name        Name of file upload field.
    * @param    integer type        Type of field.
    * @param    integer validation  Type of validation.
    * @param    string  attr        Additional HTML attributes, e.g. "id=main"
    */

    public function datetime5($name, $type = "ISO", $validate = "NORMAL", $attr = false)
    {
        // Define constants for validation
        @define("FORM_INCORRECT_HOUR",      "Specified hour is incorrect.");
        @define("FORM_INCORRECT_MINUTE",    "Specified minute is incorrect.");

        // Output date fields
        $this->date3($name, $type, $validate, $attr);

        // Get value
        $value = $this->get($name);

        // Extract time parts and save
        $this->set("${name}__hh", substr($value, 11, 2));
        $this->set("${name}__nn", substr($value, 14, 2));

        // Update the hidden field that contains datetime afterwards. Require javascript.
        $fname = $this->name;
        $this->extra_code_post .= "if (document.$fname.${name}__yy.value) document.$fname.$name.value = ('0000' + document.$fname.${name}__yy.value).substr(document.$fname.${name}__yy.value.length, 4) + '-' + ('00' + document.$fname.${name}__mm.value).substr(document.$fname.${name}__mm.value.length, 2) + '-' + ('00' + document.$fname.${name}__dd.value).substr(document.$fname.${name}__dd.value.length, 2) + ' ' + ('00' + document.$fname.${name}__hh.value).substr(document.$fname.${name}__hh.value.length, 2) + ':' + ('00' + document.$fname.${name}__nn.value).substr(document.$fname.${name}__nn.value.length, 2);";

        // Save old field class
        $class_fields = $this->class_fields;

        // Replace class_fields
        $this->class_fields = $this->class_datefields;

        // Output time fields
        echo " &nbsp; ";
        $this->text("${name}__hh", "size=2");
        echo " : ";
        $this->text("${name}__nn", "size=2");

        // Restore old field class
        $this->class_fields = $class_fields;

        // Validate, correct date or empty
        if ($validate != "NONE") {

            list ($h, $n) = array ('¤¤', '¤');

            $this->validate("!((!$h.value.length  &&  !$n.value.length)  ||  ($h.value >= 0  &&  $h.value <= 59))", FORM_INCORRECT_HOUR,   $h);
            $this->validate("!((!$h.value.length  &&  !$n.value.length)  ||  ($n.value >= 0  &&  $n.value <= 59))", FORM_INCORRECT_MINUTE, $n);
        }

        // Not empty
        if ($validate == "NORMAL") {

            // Date cannot be empty - validated by date3()
            $this->validate("!$h.value.length  &&  !$n.value.length",  FORM_MUST_SPECIFY_DATE, "¤¤¤¤¤");
        }

        // Get last data type and adjust
        list($y, $m, $d) = $this->last_datetime_type;
        $y .= '¤¤';
        $m .= '¤¤';
        $d .= '¤¤';

        // Save datetimetype if we desire to validate further
        $this->last_datetime_type = array($y, $m, $d, $h, $n);
    }




    /**
    * Set focus on last element.
    */

    public function focus() // Set focus on last element
    {
        $this->focus_element = sizeof($this->elements) - 1;
    }




    /**
    * Execute javascript before submitting form.
    *
    * @param    string  code        Javascript
    */

    public function js_before_submit($code)
    {
        $this->extra_code_post .= "\n$code\n";
    }




    /**
    * Validate (with javascript) last field as by regular expression.
    *
    * @param    string  name            Name for this check - function only written once
    * @param    string  reg_exp         Regular expression
    * @param    bool    success         True: reg_exp is success criterie,
    * @param    string  errmsg          Error message to show if e-mail is incorrect.
    * @param    string  allowblank      Allow user not to enter e-mail.
    * @param    string  criteria        Additional fail criteria
    */

    public function validate_regexp($name, $reg_exp, $success, $errmsg, $allowblank=null, $criteria=null)
    {
        $name = $name . "_ok";

        if (!strstr($this->extra_code_pre, "function $name(field)")) {
            $this->extra_code_pre .= "function $name(field)\n{\n";
            $this->extra_code_pre .= "  var reg_$name = $reg_exp;\n";
            $this->extra_code_pre .= "  return reg_$name.test(field.value)\n";
            $this->extra_code_pre .= "}\n\n";
        }

        if ($criteria) {
            $criteria = "&& $criteria";
        }

        if ($success) {
            $name = "!$name";
        }

        if (!$allowblank) {
            $this->validate("$name(¤) $criteria", $errmsg, '¤');
        }
        else {
            $this->validate("¤.value.length && $name(¤) $criteria", $errmsg, '¤');
        }
    }




    /**
    * Validate (with javascript) last field as e-mail address.
    *
    * @param    string  errmsg          Error message to show if e-mail is incorrect.
    * @param    string  allowblank      Allow user not to enter e-mail.
    * @param    string  criteria        Additional fail criteria
    */

    public function validate_email($errmsg, $allowblank=null, $criteria=null)
    {
        $this->validate_regexp("email", "/^[-\{\}\|\*\+\$\^\?!#%&'\/=_`~a-zA-Z0-9]+(\.[-\{\}\|\*\+\$\^\?!#%&'\/=_`~a-zA-Z0-9]+)*@(([a-zA-Z0-9]+\.)|([a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.))+[a-zA-Z]{2,6}$/", true, $errmsg, $allowblank, $criteria);
    }




    /**
    * Validate (with javascript) last field as host name.
    *
    * @param    string  errmsg          Error message to show if e-mail is incorrect.
    * @param    string  allowblank      Allow user not to enter e-mail.
    * @param    string  criteria        Additional fail criteria
    */

    public function validate_host($errmsg, $allowblank=null, $criteria=null)
    {
        $this->validate_regexp("host", "/^(([a-zA-Z0-9]+\.)|([a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.))+[a-zA-Z]{2,6}$/", true, $errmsg, $allowblank, $criteria);
    }




    /**
    * Validate (with javascript) last field as host name or *.host.
    *
    * @param    string  errmsg          Error message to show if e-mail is incorrect.
    * @param    string  allowblank      Allow user not to enter e-mail.
    * @param    string  criteria        Additional fail criteria
    */

    public function validate_host_wild($errmsg, $allowblank=null, $criteria=null)
    {
        $this->validate_regexp("host", "/^(\*\.)?(([a-zA-Z0-9]+\.)|([a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.))+[a-zA-Z]{2,6}$/", true, $errmsg, $allowblank, $criteria);
    }




    /**
    * Validate (with javascript) last field as IP address.
    *
    * @param    string  errmsg          Error message to show if ip address is incorrect.
    * @param    string  allowblank      Allow user not to enter ip address.
    * @param    string  criteria        Additional fail criteria
    */

    public function validate_ip($errmsg, $allowblank=false, $criteria=null)
    {
        $this->validate_regexp("ip", "/^(0*([1-9][0-9]?|1[0-9]{2}|2[0-4][0-9]|25[0-4]))\.(0*([1-9][0-9]?|1[0-9]{2}|2[0-4][0-9]|25[0-4]))\.(0*([1-9][0-9]?|1[0-9]{2}|2[0-4][0-9]|25[0-4]))\.(0*([1-9][0-9]?|1[0-9]{2}|2[0-4][0-9]|25[0-4]))$/", true, $errmsg, $allowblank, $criteria);
    }




    /**
    * Validate (with javascript) last field as URL.
    *
    * @param    string  errmsg          Error message to show if url is incorrect.
    * @param    string  allowblank      Allow user not to enter ip address.
    * @param    string  criteria        Additional fail criteria
    */

    public function validate_url($errmsg, $allowblank=false, $criteria=null)
    {
        $this->validate_regexp("ip", "/^(http|https|ftp):\/\//", true, $errmsg, $allowblank, $criteria);
    }




    /**
    * Validate (with javascript) last field as an integer.
    *
    * @param    string  min       Minimum value or null.
    * @param    string  max       Maximum value or null.
    * @param    string  errmsg    Show this message if last criteria fails.
    */

    public function validate_integer($min, $max, $errmsg, $allowblank=false)
    {
        $allowblank_string = $allowblank ? "¤.value.length && " : "";

        $this->Validate('!(/^[0-9]*$/.test(¤.value))', $errmsg);
        if (!is_null($min)) {
            $this->Validate($allowblank_string . "¤.value < $min", $errmsg, "¤");
        }
        if (!is_null($max)) {
            $this->Validate($allowblank_string . "¤.value > $max", $errmsg, "¤");
        }
    }




    /**
    * Validate (with javascript) last field as a string.
    *
    * @param    string  min       Minimum value or null.
    * @param    string  max       Maximum value or null.
    * @param    string  errmsg    Show this message if last criteria fails.
    */

    public function validate_string($min, $max, $errmsg, $allowblank=false)
    {
        $allowblank_string = $allowblank ? "¤.value.length && " : "";

        if (!is_null($min)) {
            $this->Validate($allowblank_string . "¤.value.length < $min", $errmsg, "¤");
        }
        if (!is_null($max)) {
            $this->Validate($allowblank_string . "¤.value.length > $max", $errmsg, "¤");
        }
    }




    /**
    * Validate (with javascript) last three fields as a date set.
    *
    * @param    string  min       Minimum value (ISO format) or null.
    * @param    string  max       Maximum value (ISO format) or null.
    * @param    string  errmsg    Show this message if last criteria fails.
    */

    public function validate_date($min, $max, $errmsg, $allowblank=false)
    {
        // Get order
        list($y, $m, $d) = $this->last_date_type;

        // Generate allowblank string
        $allowblank_string = $allowblank ? "(¤.value.length || ¤¤.value.length || ¤¤¤.value.length) && " : "";

        // Validate
        if (!is_null($min)) {
            $this->Validate("('0000' + $y.value).substr($y.value.length, 4) + '-' + ('00' + $m.value).substr($m.value.length, 2) + '-' + ('00' + $d.value).substr($d.value.length, 2) < '$min'", $errmsg);
        }
        if (!is_null($max)) {
            $this->Validate("('0000' + $y.value).substr($y.value.length, 4) + '-' + ('00' + $m.value).substr($m.value.length, 2) + '-' + ('00' + $d.value).substr($d.value.length, 2) > '$max'", $errmsg);
        }
    }




    /**
    * Validate (with javascript) last field as combobox.
    *
    * Fails if first entry is $_REQUEST['']ed.
    *
    * @param    string  errmsg    Show this message if last criteria fails.
    */

    public function validate_select($errmsg)
    {
        $this->Validate('¤.selectedIndex == 0', $errmsg);
    }





    /**
    * Validate (with javascript) last fields as group of checkboxes.
    *
    * Fails if wrong number of boxes has been checked.
    *
    * @param    string  num       Number of fields in group.
    * @param    string  min       Minimum number of checked fields or null.
    * @param    string  max       Maximum number of checked fields or null.
    * @param    string  errmsg    Show this message if last criteria fails.
    */

    public function validate_checkbox_group($num, $min, $max, $errmsg)
    {
        // init
        $e = $v = $p = "";

        // Count number of checked boxes
        while ($num--) {
            $e .= '¤';
            $v .= "$p$e.checked";
            $p =  " + ";
        }

        // Validate
        if (!is_null($min)) {
            $this->Validate("$v < $min", $errmsg, $e);
        }
        if (!is_null($max)) {
            $this->Validate("$v > $max", $errmsg, $e);
        }
    }




    /**
    * Validate (with javascript) last fields as group of checkboxes.
    *
    * Fails if no item has been selected.
    *
    * @param    string  errmsg    Show this message if this criteria fails.
    */

    public function validate_radio_group($num = null, $errmsg)
    {
        if (!strstr($this->extra_code_pre, "function validate_radio_group(field)")) {
            $this->extra_code_pre .= "function validate_radio_group(field)\n{\n";
            $this->extra_code_pre .= "  for (counter = 0; counter < field.length; counter++)\n";
            $this->extra_code_pre .= "    if (field[counter].checked) return true\n";
            $this->extra_code_pre .= "  return false\n";
            $this->extra_code_pre .= "}\n\n";
        }

        return $this->validate('!validate_radio_group(¤)', $errmsg);
    }



    /**
    * Validate form with javascript.
    *
    * @param    string  fail_criteria   Partial javascript code. Eg. ¤.value>5  &&  ¤.value.lengt>3
    *                                   ¤ = this element  ¤¤ = previous element  ¤¤¤ = ....
    * @param    string  errmsg          Show this message if last criteria fails.
    * @param    string  focus_element   Element on which to focus after error message was displayed.
    */

    public function validate($fail_criteria, $errmsg, $focus_element = '¤')
    {
        // Build criteria.
        $fail_criteria = $this->build_js($fail_criteria);

        // Get name and type of focus element. (assume it is only ¤¤¤s)
        list($element, $type) = $this->elements[sizeof($this->elements) - strlen($focus_element)];
        $element = 'document.' . $this->name . ".$element";

        // element name has index, not supported by js, use element number instead
        if (strstr($element, "[")) {
            $element = 'document.' . $this->name . '.elements['.(sizeof($this->elements) - strlen($focus_element)).']';
        }

        // Output check_code
        $this->check_code .= "  if ($fail_criteria)\n";
        $this->check_code .= "  { alert(\"$errmsg\");\n";
        if (in_array($type, array ("text", "password", "textarea", "file"))) {
            $this->check_code .= "    $element.select();\n";
        }
        if ($type != "checkbox" && $type != "radio") {
            $this->check_code .= "    $element.focus();\n";
        }
        $this->check_code .= "    return false;\n";
        $this->check_code .= "  }\n\n";
    }




    /**
    * Get field value.
    *
    * @param    string  key     Array index or class variable.
    */

    protected function get($key)
    {
        // Recursive - get value from name[i][j][k]
        if (preg_match('#^(.*)\[(.+)\]$#', $key, $regs)) {
            $value = $this->get($regs[1]);
            return @$value[$regs[2]];
        }

        // Multi selects - has name[] - remove the [] part
        $key = str_replace('[]', '', $key);

        // Return value from array
        if (is_array($this->values)) {
            return @$this->values[$key];
        }

        // Return value from object
        return @$this->values->$key;
    }




    /**
    * Set field value.
    *
    * @param    string  key     Array index or class variable.
    */

    protected function set($key, $value)
    {
        if (is_array($this->values)) {
            @$this->values[$key] = $value;
        }
        else {
            @$this->values->$key = $value;
        }
    }




    /**
    * Convert fail criteria (or other code) to javascript.
    *
    * @return   string      javascript code
    */

    protected function build_js($fail_criteria)
    {
        // Get number of form elements
        $num = sizeof($this->elements);

        $result = '';

        foreach (explode("\r", preg_replace("#(¤+)#", "\r\\1\r",  $fail_criteria)) as $part) {
            if ($part  &&  $part[0] == '¤') {
                $len = strlen($part);
                list($element) = $this->elements[$num - $len];

                // element name has index, not supported by js, use element number instead
                if (strstr($element, "[")) {
                    $element = 'elements['.($num - $len).']';
                }
                $result .= 'document.' . $this->name . ".$element";
            }
            else {
                $result .= $part;
            }
        }

        return $result;
    }

}





//////////////////////////////////////////////////////////////////////////////
// Tool Tips /////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


/**
* HTML/JS ToolTip Generator.
*
* NOTE: Must specify valid <!DOCTYPE to work under MSIE.
*
* Define css class like this:
*
* .tooltip {
*     font: 10pt/125% Verdana, sans-serif;
*     background: #ffffe1;
*     color: black;
*     border: black 1px solid;
*     margin: 2px;
*     padding: 10px;
*     position: absolute;
*     top: 10px;
*     left: 10px;
*     z-index: 10000;
*     visibility: hidden;
* }
*/


class tool_tips
{

    protected $tooltips;



    /**
    * Constructor
    *
    * Outputs javascripts
    * NOTE: Must specify valid <!DOCTYPE to work under MSIE.
    */

    public function __construct()
    {
        // Init
        $this->tooltips = array ();

        // Output javascript
        ?>

        <script type="text/javascript">
        <!--

        function showTooltip(id, event, timeDelay, offsetX, offsetY) {

            if (!document.getElementById) {
                return;
            }

            var target  = window.event ? window.event.srcElement : (event.target.tagName ? event.target : event.target.parentNode);
            if (target && target.removeAttribute) {
                target.title = "";
                target.removeAttribute("alt");
            }

            var tooltip = getTooltip(id);
            timeDelay = (timeDelay != null) ? timeDelay : 300;
            var x,y;

            if (typeof event.pageX == "number") {
                x = event.pageX;
                y = event.pageY;
            }
            else if (typeof event.clientX == "number") {
                x = event.clientX + getScrollLeft();
                y = event.clientY + getScrollTop();
            }

            x += (offsetX != null) ? offsetX : 10;
            y += (offsetY != null) ? offsetY : 10;

            var adjX = x + tooltip.el.offsetWidth  - getViewportWidth()  + 40 - getScrollLeft();
            var adjY = y + tooltip.el.offsetHeight - getViewportHeight() + 20 - getScrollTop();

            if (adjX > 0) {
                x -= adjX;
            }
            if (adjY > 0) {
                y -= adjY;
            }

            tooltip.el.style.left = x +"px";
            tooltip.el.style.top  = y +"px";

            Tooltips[id].show(timeDelay);
        }

        Tooltip = function(el) {
            this.id = el.id;
            this.el = el;
            this.blocked = false;
        };

        Tooltips = new Object();

        function getTooltip(id) {
            if (!Tooltips[id])
                Tooltips[id] = new Tooltip(document.getElementById(id));
            return Tooltips[id];
        }

        function hideTooltip(id, timeDelay) {
            if (!document.getElementById)
                return;
            getTooltip(id).hide();
        }

        Tooltip.prototype.show = function(millis) {
            if (this.blocked)
                return;
            this.blocked = true;
            if (window.tooltipTimer)
                clearTimeout(window.tooltipTimer);
            window.tooltipTimer = setTimeout("Tooltips."+this.id+".el.style.visibility='visible';", millis);
            setTimeout("Tooltips."+this.id+".blocked = false;", 1 + millis);
        };

        Tooltip.prototype.hide = function() {
            if (window.tooltipTimer)
                clearTimeout(window.tooltipTimer);
            Tooltips[this.id].el.style.visibility='hidden';
            Tooltips[this.id].blocked = false;
        };


        function getViewportHeight() {
            if (window.innerHeight)
                return window.innerHeight;

            if (typeof window.document.documentElement.clientHeight == "number")
                return window.document.documentElement.clientHeight;

            return window.document.body.clientHeight;
        }

        function getViewportWidth() {
            if (window.innerWidth)
                return window.innerWidth -16;

            if (typeof window.document.documentElement.clientWidth == "number")
                return window.document.documentElement.clientWidth;

            return window.document.body.clientWidth;
        }

        function getScrollLeft() {
            if (typeof window.pageXOffset == "number")
                return window.pageXOffset;

            if (document.documentElement.scrollLeft)
                return Math.max(document.documentElement.scrollLeft, document.body.scrollLeft);
            else if (document.body.scrollLeft != null)
                return document.body.scrollLeft;
            return 0;
        }

        function getScrollTop() {
            if (typeof window.pageYOffset == "number")
                return window.pageYOffset;

            if (document.documentElement.scrollTop)
                return Math.max(document.documentElement.scrollTop, document.body.scrollTop);
            else if (document.body.scrollTop != null)
                return document.body.scrollTop;
            return 0;
        }

        // -->
        </script>
        <?php
    }




    /**
    * Create tooltip and return onmouseover code for <a>
    *
    * Converts wide pure text to some 300px wide table
    */

    public function create($html, $delay = '', $maxwidth = 300)
    {
        // Convert pure text to table
        if (!strstr($html, "<") && (strlen($html) > $maxwidth/5)) {
            $html = "<table cellpadding='0' cellspacing='0' border='0' width='$maxwidth'><tr><td align='left'>$html</td></tr></table>";
        }

        // Add tooltip
        $this->tooltips[] = $html;

        // Calc new id
        $id = sizeof($this->tooltips) - 1;

        // Delay
        if ($delay) {
            $delay = ", $delay";
        }

        // Return onmouseover code
        return "onmouseover='showTooltip(\"tooltip$id\", event$delay);' onmouseout='hideTooltips();'";
    }




    /**
    * Finaliste tooltips - Call after last tooltip was created
    *
    * Outputs javascripts
    * Use </center> before this function!
    */

    public function done()
    {
        // Global onmousedown
        if (!isset($this->tooltips)) {
            return;
        }

        echo '
        <script type="text/javascript">
        <!--

        document.onmousedown = hideTooltips;
        ';

        echo 'function hideTooltips(e) { ';
        foreach ($this->tooltips as $id => $html) {
            echo  "hideTooltip('tooltip$id');";
        }

        echo "
        }

        // -->
        </script>";

        // Output tooltips
        foreach ($this->tooltips as $id => $html) {
            echo "\n<div id='tooltip$id' class='tooltip'>$html</div>\n";
        }
    }

}


?>