		<?php 
			if($total>$minimumshow)
			{	
				$next=$curentPage+1;
				$previous=$curentPage-1;
				 print "<br><center><div style='font-size:10px;'>Total Record(s) <b>".$total."</b> Display on this page:";
				 ?>
				 <select name="dispaly" onChange="window.open('<?php print $page_file;?>&dispalypages='+this.value,'_self','');">
					<?php
					for($pageLoop=1;$pageLoop<101;$pageLoop++)
					{	
						$pageLoop=$pageLoop+9;
						$select="";
						if($pageLoop==$display)
							$select="selected";
						print "<option value='$pageLoop' $select>$pageLoop</option>";
					}
					?>
				</select><br><br>
				 <?php
				if($limitFrom>0)
				{
					print "<a href='".$page_file."&Previous=1&pageing=".$previous.$link."'>Previous</a>";
				}
				else
					print "Previous";	
	
				
				$pageLink=$total/$display;
				 if(!is_int($pageLink))
					$pageLink=$pageLink+1;
				print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				for ($pageL = 1; $pageL <= $pageLink; $pageL++) 
				{
					if( $pageL == $curentPage )
						print "<b> $pageL </b> &nbsp;";
					else
						print "<a href='".$page_file."&pageing=".$pageL.$link."'>$pageL</a> &nbsp;";
				}
				print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				
				if($limitFrom+$display<$total)
				{
					print "<a href='".$page_file."&pageing=".$next.$link."'>Next</a>";
				}
				else
					print "Next";	
					
					print "</div></center>";
	
			}
			?>
			
