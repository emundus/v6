INSERT INTO `#__eb_fields` (`id`, `event_id`, `name`, `title`, `description`, `field_type`, `required`, `values`, `default_values`, `fee_field`, `fee_values`, `fee_formula`, `display_in`, `rows`, `cols`, `size`, `css_class`, `field_mapping`, `ordering`, `published`, `language`, `datatype_validation`, `extra_attributes`, `access`, `show_in_list_view`, `depend_on_field_id`, `depend_on_options`, `max_length`, `place_holder`, `multiple`, `validation_rules`, `validation_error_message`, `is_core`, `fieldtype`) VALUES
(1, -1, 'first_name', 'First Name', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', '', 1, 1, '*', 0, '', 1, 0, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(2, -1, 'last_name', 'Last Name', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 2, 1, '*', 0, '', 1, 0, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(3, -1, 'organization', 'Organization', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 3, 1, '*', 0, '', 1, 0, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(4, -1, 'address', 'Address', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 4, 1, '*', 0, '', 1, 0, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(5, -1, 'address2', 'Address 2', '', 1, 0, '', '', 0, '', '', 3, 0, 0, 0, '', NULL, 5, 0, '*', 0, '', 1, 0, 0, '', 0, NULL, 0, '', NULL, 1, 'Text'),
(6, -1, 'city', 'City', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 6, 1, '*', 0, '', 1, 0, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(7, -1, 'zip', 'Zip', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 7, 1, '*', 0, '', 1, 0, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Text'),
(8, -1, 'country', 'Country', '', 3, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 8, 1, '*', 0, '', 1, 0, 0, '', 0, NULL, 0, 'validate[required]', NULL, 1, 'Countries'),
(9, -1, 'state', 'State', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 9, 1, '*', 0, '', 1, 0, 0, '', 0, '', 0, 'validate[required]', '', 1, 'State'),
(10, -1, 'phone', 'Phone', '', 1, 1, '', '', 0, '', '', 3, 0, 0, 0, '', NULL, 10, 1, '*', 0, '', 1, 0, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Text'),
(11, -1, 'fax', 'Fax', '', 1, 0, '', '', 0, '', '', 3, 0, 0, 0, '', NULL, 11, 0, '*', 0, '', 1, 0, 0, '', 0, '', 0, '', '', 1, 'Text'),
(12, -1, 'email', 'Email', '', 1, 1, '', '', 0, '', '', 0, 0, 0, 0, '', NULL, 12, 1, '*', 0, '', 1, 0, 0, '', 0, '', 0, 'validate[required,custom[email],ajax[ajaxEmailCall]]', '', 1, 'Email'),
(13, -1, 'comment', 'Comment', '', 2, 1, '', '', 0, '', '', 3, 7, 0, 0, '', NULL, 13, 1, '*', 0, '', 1, 0, 0, '', 0, '', 0, 'validate[required]', '', 1, 'Textarea');