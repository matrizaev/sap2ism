<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo $module_name?></title>
		<link rel="stylesheet" type="text/css" href="tree_style.css">
		<meta name="author" content="Matrizaev Vyacheslav">
		<script>
			function getFileDcount(fid)
			{
				var dcount_element = document.getElementById(fid);
				var dcount_value = +document.getElementById(fid).innerHTML;
				dcount_value++;
				dcount_element.innerHTML = dcount_value;
			}
			function DeleteElementByID(fid)
			{
				var xmlhttp;
				if (window.XMLHttpRequest)
				{// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				}
				else
				{// code for IE6, IE5
					xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange=function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						if (xmlhttp.responseText == "TRUE")
						{
							var element = document.getElementById(fid);
							element.parentNode.removeChild(element);
						}
						else
						{
							alert("Не удалось.");
						}
					}
				}
				xmlhttp.open("GET","orders_delete.php?id="+fid,true);
				xmlhttp.send();
			}
		</script>
	</head>
	<body>
		<div class="OuterContent">
			<div class="HeaderWrapper">
				<div class="Header">
					<?php echo $title; ?>
				</div>
			</div>
			<div class="Top">
				<div class="Search">
					<form action="<?php echo $module_search_string; ?>" method="get">
						<div>
							<input type="text" class="InputBox" name="searchword" size="15" placeholder="Введите текст...">
							<?php
									if(isset($type))
										echo '<input type="hidden" name="type" value="'.$type.'">';
									if(isset($ou))
										echo '<input type="hidden" name="ou" value="'.$ou.'">';
									if(isset($dep))
										echo '<input type="hidden" name="dep" value="'.$dep.'">';
									if(isset($year))
										echo '<input type="hidden" name="year" value="'.$year.'">';
							?>
							<input type='hidden' name='ref' value="<?php echo (isset($ref)?$ref:0) ?>">
							<input type="submit" value="Поиск" class="Button">
						</div>
					</form>
				</div>
				<div class="ParentCategory">
					<?php echo $path_str; ?>
				</div>
			</div>
			<div class="Content">
				<?php echo $content; ?>
			</div>
		</div>
	</body>
</html>