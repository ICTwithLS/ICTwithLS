function validate()
{
    var username=document.getElementById("username").value;
    var password=document.getElementById("password").value;

    //Student
    if(username=="student"&& password=="ictls@01")
    {
        window.open("students.html")
        return false;
    }

    //Paper Panel
    if(username=="paneluser"&& password=="lsictpanel@marking1")
    {
        window.open("Paper Panel.html")
        return false;
    }
    else
    {
        alert("Invalid Username or Password!")
        return true;
    }
}

function msg()
{
    alert('Contact Admin [0786076637]')
    
}
