<?php if (isset($arResult['CREATED_OK']) && $arResult['CREATED_OK'] === true):?>
	<p class="created_ok">Создание меню прошло успешно!</p>
<?endif;?>

<div class="menu_scaner">
	<form method="POST">
		<?php if ($arResult['ERROR']):?>
			<?php echo ShowMessage($arResult['ERROR']);?>
		<?php endif;?>

		<?php if ( isset($arResult['INFO']) ):?>
			<div class="item">
				<label>Тип меню:</label>
				<strong><?php echo $arResult['INFO']['MENU_TYPE'];?></strong>
			</div>
			
			<div class="item">
				<label>Папка для сканирования:</label>
				<strong><?php echo $arResult['INFO']['FOLDER_FILTER'];?></strong>
	
				<?php if ($arResult['INFO']['SUBFOLDERS'] === true):?>
					(рекурсивно)
				<?php endif;?>
			</div>

			<?php if (count($arResult['INFO']['IGNORE']) > 0):?>
				<div class="item">
					<label>Игнор лист</label>
					<ul>
						<?php foreach ($arResult['INFO']['IGNORE'] as $v):?>
							<li><strong><?php echo $v;?></strong></li>
						<?php endforeach;?>
					</ul>
				</div>
			<?php endif;?>

			<?php if ($arResult['PARAMS']['menu_create'] == MENU_CREATE_GLOBAL):?>
				<div class="item">
					Файл меню <strong><?php echo $arResult['INFO']['MENU_ROOT'];?></strong>
					
					<?php if (file_exists($arResult['INFO']['MENU_ROOT']) ):?>
						<?php if (is_writable($arResult['INFO']['MENU_ROOT'])):?>
							(будет перезаписано)
						<?php else: ?>
							(запрет на запись)
						<?php endif; ?>
					<?php else:?>
						(будет создано)
					<?php endif;?>

					<input type="hidden" name="menu[]" value="<?php echo $arResult['INFO']['MENU_ROOT'];?>" />
				</div>

				<div class="item">
					<label>Ссылки меню</label>
					<div class="links">
						<ul>
							<?php foreach ($arResult['ITEMS'] as $arItem):?>
								<?php foreach ($arItem['FILES'] as $link):?>
									<li>
										<input type="checkbox" name="created[0][]" checked="checked" /> <a href="<?php echo $link;?>" target="_blank"><?php echo $link;?></a>
										<input type="hidden" name="link[0][]" value="<?php echo $link;?>" />
									</li>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			<?php else:?>
				<?php foreach ($arResult['ITEMS'] as $menuId => $arItem):?>
					<div class="item links">
						<?php $folderMenu = $arItem['DIR'] . $arResult['INFO']['MENU_FILE'];?>
						Файл меню <strong><?php echo $folderMenu;?></strong>
						
						<?php if (file_exists($folderMenu) ):?>
							<?php if (is_writable($folderMenu)):?>
								(будет перезаписано)
							<?php else: ?>
								(запрет на запись)
							<?php endif; ?>
						<?php else:?>
							(будет создано)
						<?php endif;?>

						<input type="hidden" name="menu[<?php echo $menuId; ?>]" value="<?php echo $folderMenu;?>" />
					</div>

					<div class="item">
						<label>Ссылки меню</label>
						<div class="links">
							<ul>
								<?php foreach ($arItem['FILES'] as $link):?>
									<li>
										<input type="checkbox" name="created[<?php echo $menuId; ?>][]" checked="checked" /> <a href="<?php echo $link;?>" target="_blank"><?php echo $link;?></a>
										<input type="hidden" name="link[<?php echo $menuId; ?>][]" value="<?php echo $link;?>" />
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>

					<hr />
				<?php endforeach; ?>
			<?php endif;?>

			<div class="item">
				<input type="button" name="cancel" value="Отмена" onclick="window.location.reload();" />
				<input type="submit" name="create" value="Создать меню" />
			</div>
		<?php else: ?>	
	
			<div class="item">
				<label>Папка для сканирования</label>
				<input type="text" name="s[folder]" value="<?php echo $arResult['PARAMS']['folder'];?>" />
			</div>

			<div class="item">
				<label>Шаблон файлов</label>
				<input type="text" name="s[filter]" value="<?php echo $arResult['PARAMS']['filter'];?>" />
			</div>

			<div class="item">
				<label>Игнорировать пути (регистрозависимо)</label>
				<textarea name="s[ignore]"><?php echo $arResult['PARAMS']['ignore'];?></textarea>
			</div>

			<div class="item">
				<label for="menu_global">Одно общее меню</label>
				<input type="radio" id="menu_global" name="s[menu_create]" value="global" <?php echo $arResult['PARAMS']['menu_create'] == MENU_CREATE_GLOBAL ? 'checked="checked"' : '';?> />
				<br />
				<label for="menu_local">Для каждого раздела</label>
				<input type="radio" id="menu_local" name="s[menu_create]" value="local" <?php echo $arResult['PARAMS']['menu_create'] == MENU_CREATE_LOCAL ? 'checked="checked"' : '';?> />
			</div>

			<div class="item">
				<label>Тип меню</label>
				<select name="s[menu_type]">
					<?php foreach ($arResult['MENUS'] as $type => $value):?>
						<option value="<?php echo $type;?>" <?php echo $type == $arResult['PARAMS']['menu_type'] ? 'selected="selected"' : '' ?>><?php echo $value; ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="item">
				<label for="subfolders">Сканировать подразделы (рекурсивно)</label>
				<input type="checkbox" name="s[subfolders]" id="subfolders" <?php echo $arResult['PARAMS']['subfolders'] ? 'checked="checked"' : '';?>  />
			</div>

			<div class="item">
				<input type="reset" name="reset" value="Очистить " />
				<input type="submit" name="scan" value="Сканировать" />
			</div>
		<?php endif; ?>
	</form>
</div>
