using System;
using System.Data;
using System.Configuration;
using System.Collections;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using System.IO;
using System.Web.Configuration;

public partial class WorldWindMaps : System.Web.UI.Page
{
    protected void Page_Load(object sender, EventArgs e)
    {

        int X = Convert.ToInt32(Request.QueryString["X"].ToString());
        int Y = Convert.ToInt32(Request.QueryString["Y"].ToString());
        int L = Convert.ToInt32(Request.QueryString["L"].ToString());
        string T = Request.QueryString["T"].ToString(); //T = Dataset in WorldWind parlance

        string filePath = WebConfigurationManager.AppSettings[T];

        string fileExt = WebConfigurationManager.AppSettings[T + "_EXT"];

        filePath += L.ToString() + "\\" + Y.ToString("0000") + "\\" + Y.ToString("0000") + "_" + X.ToString("0000") + fileExt;

        FileInfo file = new FileInfo(filePath);


        if (!file.Exists && T == "SRTM")
        {
            throw new HttpException(404, "Not Found");
            Response.End();
        }

        else if (!file.Exists)
        {
            
		string blankfilepath = WebConfigurationManager.AppSettings[T] + "0" + "\\" + "0000" + "\\" + "blank" + fileExt;
		FileInfo blankfile = new FileInfo(blankfilepath);
      	
		Response.ClearContent();
        	Response.AddHeader("Content-Length", blankfile.Length.ToString());
        	Response.ContentType = WebConfigurationManager.AppSettings[T + "_MIME"];
        	Response.TransmitFile(blankfile.FullName);
        }
	else
	{

        	Response.ClearContent();
        	Response.AddHeader("Content-Length", file.Length.ToString());
        	Response.ContentType = WebConfigurationManager.AppSettings[T + "_MIME"];
        	Response.TransmitFile(file.FullName);
	}
    }
}

