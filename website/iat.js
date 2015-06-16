function iat (title1, title2, title3, title4, array1, array2, array3, array4) {
	randomizing = Math.floor(Math.random() * 4);
	switch (randomizing) {
		case 0:
			name1 = title1;
			name2 = title2;
			name3 = title3;
			name4 = title4;
			arr1 = array1;
			arr2 = array2;
			arr3 = array3;
			arr4 = array4;
			break;
		case 1:
			name1 = title1;
			name2 = title2;
			name3 = title4;
			name4 = title3;
			arr1 = array1;
			arr2 = array2;
			arr3 = array4;
			arr4 = array3;
			break;
		case 2:
			name1 = title2;
			name2 = title1;
			name3 = title3;
			name4 = title4;
			arr1 = array2;
			arr2 = array1;
			arr3 = array3;
			arr4 = array4;
			break;
		case 3:
			name1 = title2;
			name2 = title1;
			name3 = title4;
			name4 = title3;
			arr1 = array2;
			arr2 = array1;
			arr3 = array4;
			arr4 = array3;
			break;
	}

	matrixReturn = [[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]],[[],[],[]]];
	i = 0;
	numTrials = 20; 																		//Current/starting trials
	curBlock = 1; 																		//Current block on; always start with 1
	curTrial = 1; 																		//Current trial on; always start with 1
	c = "?"
	start = 0;
	diff = 0;

	$("#error").hide();
	$(document).keyup(function(e) {
		$("#error").hide();
		switch (curBlock) {
			case 1:
				arrLeft = arr1;
				arrRight = arr2;
				break;
			case 2:
				arrLeft = arr3;
				arrRight = arr4;					 
				break;
			case 3:
				arrLeft = arr1.concat(arr3);
				arrRight = arr2.concat(arr4);
				break;
			case 4:
				arrLeft = arr1.concat(arr3);
				arrRight = arr2.concat(arr4);
				break;
			case 5:
				arrLeft = arr2;
				arrRight = arr1;
				break;
			case 6:
				arrLeft = arr2.concat(arr3);
				arrRight = arr1.concat(arr4);
				break;
			case 7:
				arrLeft = arr2.concat(arr3);
				arrRight = arr1.concat(arr4);
				break;
			default:
				c = "DONE";
				break;				
		}
		if (e.which == 32 && c == "?") {
			date = new Date();
			seconds = date.getTime()/1000;
			start = seconds;
			
			c = Math.floor(Math.random() *10);
			if(c < 5){ 															//If random number < 5, pick from left array
				randNeg = Math.floor(Math.random() * arrLeft.length);								//Choose random number from 0 to left array length
				item = arrLeft[randNeg];
			} 																			//Ends if statement to check if random number < 5
			else { 														//If random number >= 5, pick from right array
				randPos = Math.floor(Math.random() * arrRight.length);							//Choose random number from 0 to right array length
				item = arrRight[randPos];
				
			}																			//Ends else if to choose positive word
			if (item.indexOf(".") < 0) {
				$("#console").html(item);									//Show word at that index	
			} else {
				$("#console").html("<img src=\"images/"+item+"\">");									//Show word at that index
			}
			matrixReturn[curBlock-1][0][i] = item;
		} else if ((e.which == 69 || e.which == 73) && c != "?") {
			date = new Date();
			seconds = date.getTime()/1000;
			diff = seconds - start; // time to select an answer
			start = seconds;
			matrixReturn[curBlock-1][1][i] = diff;
			if ((e.which == 69 && c < 5) || (e.which == 73 && c >= 5)) {
				matrixReturn[curBlock-1][2][i] = 0;
				curTrial++;
				if (curTrial > numTrials) {
					$("#error").hide();																		
					curTrial = 1;
					curBlock++;
					if (curBlock == 1 || curBlock == 2 || curBlock == 3 || curBlock == 6) {
						numTrials = 20;
					} else if (curBlock == 4 || curBlock == 5 || curBlock == 7) {
						numTrials = 40;
					}
					c = "?";
					$("#console").html("");
					i = 0;
				} else {
					c = Math.floor(Math.random() *10);
					if(c < 5){ 															//If random number < 5, pick from left array
						randNeg = Math.floor(Math.random() * arrLeft.length);								//Choose random number from 0 to left array length
						item = arrLeft[randNeg];
					} 																			//Ends if statement to check if random number < 5
					else { 														//If random number >= 5, pick from right array
						randPos = Math.floor(Math.random() * arrRight.length);							//Choose random number from 0 to right array length
						item = arrRight[randPos];
						
					}																			//Ends else if to choose positive word
					if (item.indexOf(".") < 0) {
						$("#console").html(item);									//Show word at that index	
					} else {
						$("#console").html("<img src=\"images/"+item+"\">");									//Show word at that index
					}
					matrixReturn[curBlock-1][0][i+1] = item;
					i++;
				}
			} else {
				$("#error").show();
				matrixReturn[curBlock-1][2][i] = 1;
				matrixReturn[curBlock-1][0][i+1] = item;
				i++;
			}
		}			
			
		switch (curBlock) {
			case 1:
				nameLeft = name1;
				nameRight = name2;
				break;
			case 2:
				nameLeft = name3;
				nameRight = name4;				 
				break;
			case 3:
				nameLeft = name1 + " or " + name3;
				nameRight = name2 + " or " + name4;
				break;
			case 4:
				nameLeft = name1 + " or " + name3;
				nameRight = name2 + " or " + name4;
				break;
			case 5:
				nameLeft = name2;
				nameRight = name1;
				break;
			case 6:
				nameLeft = name2 + " or " + name3;
				nameRight = name1 + " or " + name4;
				break;
			case 7:
				nameLeft = name2 + " or " + name3;
				nameRight = name1 + " or " + name4;
				break;
			default:
				c = "DONE";
				break;				
		}
		if (c != "DONE") {
			$("#directions").html(numTrials + " words will be shown. Press 'e' if the word is " + nameLeft.toLowerCase()  + ", 'i' if the word is " + nameRight.toLowerCase() + ". Press 'spacebar' to begin.");
			$("#left").html(nameLeft);
			$("#right").html(nameRight);
		} else {
			$("#console").html("You have completed the IAT");
			$("#directions").html("");
			$("#left").html("");
			$("#right").html("");
			alert(JSON.stringify(matrixReturn, null, 4));
			// /return matrixReturn;
		}
	});
	
}