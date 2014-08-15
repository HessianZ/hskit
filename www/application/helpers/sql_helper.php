<?php

/**
 * sql_helper
 * 
 * @author Hessian <hess.4x@gmail.com>
 */

function build_where( $db, $search_params, $field_prefix = "", $match_empty_string = false )
{
  if ( empty($search_params) )
    return "";
  
  $ops = array( 
    'gt' => '>',
    'lt' => '<',
    'ge' => '>=',
    'le' => '<=',
    'ne' => '<>',
    'eq' => '=',
    null => '='
  );
  
  $where_conditions = array();
  //遍历整个 searchparam 数组
  foreach( $search_params as $name => $value )
  {
    $value = trim( $value );
    
    if ( $value === '' && $match_empty_string == false )
      continue;
    
    $column_name = $name;
    $op = null;
    if ( ( $op_pos = strpos( $name, ':' ) ) !== false ) 
    {
      list ( $column_name, $op ) = explode( ':', $name );
      $op = strtolower( $op );
    }
    
    if ( isset( $ops[$op] ) )
      $where_conditions[] = "{$field_prefix}$column_name {$ops[$op]} {$db->escape( $value )}";
    else
    switch ( $op )
    {
      case '%':
      case 'like':
        $where_conditions[] = "{$field_prefix}$column_name LIKE '%{$db->escape_like_str( $value )}%'";
        break;
      case 'rlike':
        $where_conditions[] = "{$field_prefix}$column_name LIKE '%{$db->escape_like_str( $value )}'";
        break;
      case 'llike':
        $where_conditions[] = "{$field_prefix}$column_name LIKE '{$db->escape_like_str( $value )}%'";
        break;
      case 'null':
        $where_conditions[] = "{$field_prefix}$column_name IS NULL";
        break;
      case 'notnull':
        $where_conditions[] = "{$field_prefix}$column_name IS NOT NULL";
        break;

      default:
        break;
    }
  }

  //如果有过滤器，则返回 WHERE 子句
  if( ! empty( $where_conditions ) )
    return implode( " AND ", $where_conditions );
  
  //否则返回 空字符串
  return "";
}

function build_order( $orders )
{
  $order_string = '';
  
  if ( is_array( $orders ) ) {
    $orderArr = array( );
    foreach ( $orders as $field => $dir )
      $orderArr[ ] = strtolower( $dir ) == "desc" ? "`$field` $dir" : $field;

    if ( !empty( $orderArr ) )
      $order_string = implode( ",", $orderArr );
  } else {
    $order_string = $orders;
  }
  
  return $order_string;
}
?>
