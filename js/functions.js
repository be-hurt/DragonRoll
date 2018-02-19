//Initialize the jquery popup for use
$(document).ready(function() {

    // Initialize the plugin
    $('#my_popup').popup();
    $('#add_item_popup').popup();
    $('#remove_item_popup').popup();
    $('#wpn_popup').popup();
    $('#wpn_equip_popup').popup();
    $('#wpn_unequip_popup').popup();
    $('#remove_wpn_popup').popup();
    $('#armor_popup').popup();
    $('#armor_equip_popup').popup();
    $('#armor_unequip_popup').popup();
    $('#remove_armor_popup').popup();

});


//Simulate the rolling of 4 6-sided dice: drop the lowest number of each and repeat the process 6 times to get an array to be used for assigning to character stats
function roll_stat() {
    var stat = 0;
    var stats_array = [];
    var roll_array = [];

    for (var i = 0; i < 6; i++) {

        //return a random number between 1 and 6 four times
        for (var count = 0; count < 4; count++) {
            var random_roll = Math.floor(Math.random() * 6) + 1;
            roll_array.push(random_roll);
        }
        //get rid of the lowest number of the 4 returned
        var lowest_num = Math.min(...roll_array);
        var lowest_num_index = roll_array.indexOf(lowest_num);
        roll_array.splice(lowest_num_index, 1);

        //Add the numbers from roll_array together
        var stat_num = roll_array.reduce(function(a, b){return a + b;});

        //empty roll_array for next iteration
        roll_array = [];

        //Add the var stat_num to the stats_array to be used
        stats_array.push(stat_num);
    }

    var mystring = stats_array.toString();
    var el = document.getElementsByClassName("base_stat");

    for (var i = 0; i < stats_array.length; i++) {
        el[i].innerHTML = stats_array[i];
    }

    //add hidden input fields to carry over the values rolled if there is not one currently set. Will have to do this individually for each stat (due to the name).
    //Not sure how this can be reduced / made less hideous
    if(!document.getElementById("str")){
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("id", "str");
        input.setAttribute("name", "str");
        input.setAttribute("value", stats_array[0]);
        document.getElementById("new_char2").appendChild(input);
    } else {
        //update the value attribute
        var strength = document.getElementById("str");
        strength.removeAttribute("value");
        strength.setAttribute("value", stats_array[0]);
    }

    if(!document.getElementById("dex")){
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("id", "dex");
        input.setAttribute("name", "dex");
        input.setAttribute("value", stats_array[1]);
        document.getElementById("new_char2").appendChild(input);
    } else {
        //update the value attribute
        var dexterity = document.getElementById("dex");
        dexterity.removeAttribute("value");
        dexterity.setAttribute("value", stats_array[1]);
    }

    if(!document.getElementById("con")){
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("id", "con");
        input.setAttribute("name", "con");
        input.setAttribute("value", stats_array[2]);
        document.getElementById("new_char2").appendChild(input);
    } else {
        //update the value attribute
        var constitution = document.getElementById("con");
        constitution.removeAttribute("value");
        constitution.setAttribute("value", stats_array[2]);
    }

    if(!document.getElementById("intel")){
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("id", "intel");
        input.setAttribute("name", "intel");
        input.setAttribute("value", stats_array[3]);
        document.getElementById("new_char2").appendChild(input);
    } else {
        //update the value attribute
        var intelligence = document.getElementById("intel");
        intelligence.removeAttribute("value");
        intelligence.setAttribute("value", stats_array[3]);
    }

    if(!document.getElementById("wis")){
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("id", "wis");
        input.setAttribute("name", "wis");
        input.setAttribute("value", stats_array[4]);
        document.getElementById("new_char2").appendChild(input);
    } else {
        //update the value attribute
        var wisdom = document.getElementById("wis");
        wisdom.removeAttribute("value");
        wisdom.setAttribute("value", stats_array[4]);
    }

    if(!document.getElementById("cha")){
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("id", "cha");
        input.setAttribute("name", "cha");
        input.setAttribute("value", stats_array[5]);
        document.getElementById("new_char2").appendChild(input);
    } else {
        //update the value attribute
        var charisma = document.getElementById("cha");
        charisma.removeAttribute("value");
        charisma.setAttribute("value", stats_array[5]);
    }
}

/*NOTE: The following functions are for demo purposes only. Will later change these to ajax calls to a php function to eliminate excessive hidden divs*/

//Create a function that will display the descriptions for the chosen character race in new_character.php
function raceDescr(choice){
   if(choice.value == 1) {
       var races = document.getElementsByClassName('race_desc');
       for(var i = 0; i < races.length; i++) {
            races[i].className = 'hidden';
       }
       document.getElementById('Halfling').className = 'race_desc';
   } else if (choice.value == 2) {
       var races = document.getElementsByClassName('race_desc');
        for(var i = 0; i < races.length; i++) {
            races[i].className = 'hidden';
       }
       document.getElementById('Dwarf').className = 'race_desc';
   } else if (choice.value == 3) {
       var races = document.getElementsByClassName('race_desc');
        for(var i = 0; i < races.length; i++) {
            races[i].className = 'hidden';
       }
       document.getElementById('Elf').className = 'race_desc';
   }
}

//Create a function that will display the descriptions for the chosen character class in new_character.php
function classDescr(choice){
   if(choice.value == 1) {
       var classes = document.getElementsByClassName('class_desc');
       for(var i = 0; i < classes.length; i++) {
            classes[i].className = 'hidden';
       }
       document.getElementById('Fighter').className = 'class_desc';
   } else if (choice.value == 2) {
       var classes = document.getElementsByClassName('class_desc');
        for(var i = 0; i < classes.length; i++) {
            classes[i].className = 'hidden';
       }
       document.getElementById('Barbarian').className = 'class_desc';
   }
}

//Create a function that will display the descriptions for the chosen character background in new_character.php
function backgroundDescr(choice){
   if(choice.value == 1) {
       var backgrounds = document.getElementsByClassName('background_desc');
       for(var i = 0; i < backgrounds.length; i++) {
            backgrounds[i].className = 'hidden';
       }
       document.getElementById('Acolyte').className = 'background_desc';
   }
}

//Create a function that will display the descriptions for the chosen character alignment in new_character.php
function alignmentDescr(choice){
   if(choice.value == 1) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(1).className = 'alignment_desc';
   } else if (choice.value == 2) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(2).className = 'alignment_desc';
   } else if (choice.value == 3) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(3).className = 'alignment_desc';
   } else if (choice.value == 4) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(4).className = 'alignment_desc';
   } else if (choice.value == 5) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(5).className = 'alignment_desc';
   } else if (choice.value == 6) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(6).className = 'alignment_desc';
   } else if (choice.value == 7) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(7).className = 'alignment_desc';
   } else if (choice.value == 8) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(8).className = 'alignment_desc';
   } else if (choice.value == 9) {
       var alignments = document.getElementsByClassName('alignment_desc');
       for(var i = 0; i < alignments.length; i++) {
            alignments[i].className = 'hidden';
       }
       document.getElementById(9).className = 'alignment_desc';
   }
}

//Create a function that will display the descriptions for the chosen character subrace in new_character2.php
function subraceDescr(choice){
   if(choice.value == 1) {
       var subraces = document.getElementsByClassName('subrace_desc');
       for(var i = 0; i < subraces.length; i++) {
            subraces[i].className = 'hidden';
       }
       document.getElementById(1).className = 'subrace_desc';
   } else if(choice.value == 2) {
       var subraces = document.getElementsByClassName('subrace_desc');
       for(var i = 0; i < subraces.length; i++) {
            subraces[i].className = 'hidden';
       }
       document.getElementById(2).className = 'subrace_desc';
   } else if(choice.value == 3) {
       var subraces = document.getElementsByClassName('subrace_desc');
       for(var i = 0; i < subraces.length; i++) {
            subraces[i].className = 'hidden';
       }
       document.getElementById(3).className = 'subrace_desc';
   }
}
