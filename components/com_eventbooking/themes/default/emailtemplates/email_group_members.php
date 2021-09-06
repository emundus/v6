<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
?>
<table class="table table-striped table-bordered table-condensed" cellspacing="0" cellpadding="0">
	<thead>
		<?php
			for ($i = 0, $n = count($rowFields); $i < $n; $i++)
			{
				$rowField = $rowFields[$i];

				if (in_array($rowField->name, ['first_name', 'last_name', 'email']) || $rowField->show_on_registrants)
				{
				?>
					<th>
						<?php echo $rowField->title; ?>
					</th>
				<?php
				}
			}
		?>
	</thead>
	<tbody>
		<?php
			foreach ($rowMembers as $rowMember)
			{
				$memberData = EventbookingHelperRegistration::getRegistrantData($rowMember, $rowFields);
			?>
				<tr>
					<?php
						for ($i = 0, $n = count($rowFields); $i < $n; $i++)
						{
							$rowField = $rowFields[$i];

							if (in_array($rowField->name, ['first_name', 'last_name', 'email']) || $rowField->show_on_registrants)
							{
							?>
								<td>
									<?php
									if ($rowField->is_core)
									{
										echo $rowMember->{$rowField->name};
									}
									else
									{
										if (isset($memberData[$rowField->name]))
										{
											echo $memberData[$rowField->name];
										}
										else
										{
											echo '';
										}
									}
									?>
								</td>
							<?php
							}
						}
					?>
				</tr>
			<?php
			}
		?>
	</tbody>
</table>