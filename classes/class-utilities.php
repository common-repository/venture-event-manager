<?php

class VentureUtility
{
    public static function vemDebug($var, $die = false, $hidden = false, $echo = true)
    {
        if ($echo) {
            if ($hidden) {
                echo '<div class="vem-debug-hidden" style="display:none">';
            }

            if (is_admin()) {
                echo '<pre style="padding-left: 182px;">';
            } else {
                echo '<pre>';
            }
            var_dump($var);
            echo '</pre>';
            if ($hidden) {
                echo '</div>';
            }

            if ($die) {
                die;
            }

        } else {
            // Ignores die
            $output = '';
            if ($hidden) {
                $output .= '<div class="vem-debug-hidden" style="display:none">';
            }

            if (is_admin()) {
                $output .= '<pre style="padding-left: 182px;">';
            } else {
                $output .= '<pre>';
            }
            $output .= var_export($var, true);
            $output .= '</pre>';
            if ($hidden) {
                $output .= '</div>';
            }

            return $output;
        }
    }

    public static function getContrastYIQ($hexcolor) {
        if ($hexcolor == "transparent" || $hexcolor == "") {
            $outputColor = '#252525'; 
        } else {
            $r = hexdec(substr($hexcolor,1,2));
            $g = hexdec(substr($hexcolor,3,2));
            $b = hexdec(substr($hexcolor,5,2));
            $yiq = (($r*299)+($g*587)+($b*114))/1000;
            $outputColor = ($yiq >= 128) ? '#000000' : '#ffffff';
        }
        return apply_filters("vem_get_contrast_color", $outputColor, $hexcolor);
    }

    public static function ventureSpinner($id = '', $style='') {
        return '<div class="venture-spinner" style="-webkit-transform:scale(0.5);'.$style.'" id="'.$id.'"><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>';
    }

    public static function removeWpAutoP($content) {
        $content = do_shortcode(shortcode_unautop( $content));
        $content = preg_replace('#^<\/p>|^<br \/>|<p>$#', '', $content);
        return $content;
    }

    public static function getSingleMeta($metaValue) {
		if (is_array($metaValue)) {
			if (sizeof($metaValue) > 0) {
				return $metaValue[0];
			} else {
				return '';
			}
		} else {
			return $metaValue;
		}
	}

    public static function getTemplate($name, $data = []) {
        if (!array_key_exists('action', $data)) {
            $data['formAction'] = esc_url(admin_url('admin-post.php'));
        }
    
        if (!array_key_exists('redirect', $data)) {
            $data['redirect'] = get_site_url().strtok($_SERVER['REQUEST_URI'], '?');
        }
    
        ob_start();
        include (plugin_dir_path(__FILE__).'../templates/'.$name.'.php');
        return ob_get_clean();
    }
    
}
