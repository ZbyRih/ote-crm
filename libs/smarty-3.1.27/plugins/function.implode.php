<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {implode} function: glue an array together as a string, with spupplied string glue, and either assign or display
 * @example {implode pieces=$my_list glue=', ' assign=$joined}
 * @example {implode pieces=$my_list limit=1} Useful to output first element of an assoc array
 *
 * Parameters (in Smarty template):
 *    pieces [required] - the array to be imploded
 *    glue [required unless limit=1] - the string to join with
 *   assign - assign to new variable rather than outputting
 *   limit - maximum number of parts in array to join
 */
function smarty_function_implode($params, $template)
{
   if (!isset($params['pieces']))
   {
      $smarty->trigger_error("implode: missing 'pieces' parameter");
      return;
   }

   if ( ! isset($params['glue']) && $params['limit'] != 1 )
   {
      $smarty->trigger_error("implode: missing 'glue' parameter");
      return;
   }

   if ( isset($params['limit']) )
   {
      if ( ! is_int($params['limit']) )
      {
         $smarty->trigger_error("implode: 'limit' parameter must be an integer");
         return;
      }

      $pieces = array_slice($params['pieces'], 0, $params['limit'], true);
   }
   else
   {
      $pieces = $params['pieces'];
   }

   if ( isset($params['assign']) )
   {
   	  $template->assign($params['assign'], implode($params['glue'], $pieces));
   }
   else
   {
      return implode($params['glue'], $pieces);
   }
}