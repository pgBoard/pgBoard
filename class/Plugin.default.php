<?php
class BoardPlugin
{
  function base_title($title) { return $title; }
  function list_prep_data($cleaned,$raw) { return $cleaned; }
  function view_prep_data($cleaned,$raw) { return $cleaned; }
}
