/**
 * @name		jQuery Cascdejs plugin
 * @author		zdy
 * @version 	1.0 
 * //目前同一个页面只支持一个效果，想要实现多个，请使用构造函数
function cascde(){
	//初始参数
	this.cascdeData = {
		a: 'a',
		b: 'b'
	}

	//执行方法
	this.cascdeInit = function(config,v1,v2,v3,v4) {
		console.log(config);
	}
}

(new cascde()).cascdeInit({dd:'dd'}, 'a', 'b', 'c', 'd');
(new cascde()).cascdeInit({aa:'aa'}, 'a', 'b', 'c', 'd');
 */

//首先需要初始化
var cascde = {
	xmlDoc: null,
	topNodeList: null,
	cityList: null,
	citys: null,
	countyNodes: null,
	nodeIndex: 0,
	childNodeIndex: 0,
	openStreet: 0,//是否打开街道
	xmlfileBase: '',
	
	//级联ID属性
	id1: 'sel-provance',
	id2: 'sel-city',
	id3: 'sel-area',
	id4: 'sel-street'
	
	//级联name属性
};

//获取xml文件
function cascdeInit(config,v1,v2,v3,v4) {
	//初始化
    cascde.id1 = config.provanceId;
    cascde.id2 = config.cityId;
    cascde.id3 = config.areaId;
    cascde.id4 = config.streetId;
//    if (config.extid) {
//    	extid = '-' + config.extid;
//    	cascde.id1 += extid;
//    	cascde.id2 += extid;
//    	cascde.id3 += extid;
//    	cascde.id4 += extid;
//    }
    
    cascde.openStreet = config.openStreet;
    cascde.xmlfileBase = config.xmlfileBase;

    //打开xlmdocm文档
    var xmlfile = cascde.xmlfileBase + 'area.xml?v=1';

    cascde.xmlDoc = loadXmlFile(xmlfile);
    var dropElement1 = document.getElementById(cascde.id1);
    var dropElement2 = document.getElementById(cascde.id2);
    var dropElement3 = document.getElementById(cascde.id3);
    var dropElement4 = document.getElementById(cascde.id4);
    RemoveDropDownList(dropElement1);
    RemoveDropDownList(dropElement2);
    RemoveDropDownList(dropElement3);
    RemoveDropDownList(dropElement4);
    if (window.ActiveXObject) {
        cascde.topNodeList = cascde.xmlDoc.selectSingleNode("address").childNodes;
    } else {
        cascde.topNodeList = cascde.xmlDoc.childNodes[0].getElementsByTagName("province");      
    }
    if (cascde.topNodeList.length > 0) {
        //省份列表
        var county;
        var province;
        var city;
        for (var i = 0; i < cascde.topNodeList.length; i++) {
            //添加列表项目
            county = cascde.topNodeList[i];          
            var option = document.createElement("option");
            option.value = county.getAttribute("name");
            option.text = county.getAttribute("name");
            if (v1 == option.value) {
                option.selected = true;
                cascde.nodeIndex = i;
            }
            dropElement1.add(option);
        }
        if (cascde.topNodeList.length > 0) {
            //城市列表
            cascde.citys = cascde.topNodeList[cascde.nodeIndex].getElementsByTagName("city")
            for (var i = 0; i < cascde.citys.length; i++) {
                var id = dropElement1.options[cascde.nodeIndex].value;
                //默认为第一个省份的城市
                province = cascde.topNodeList[cascde.nodeIndex].getElementsByTagName("city");
                var option = document.createElement("option");
                option.value = province[i] .getAttribute("name");
                option.text = province[i].getAttribute("name");
                if (v2 == option.value) {
                    option.selected = true;
                    cascde.childNodeIndex = i;
                }
                dropElement2.add(option);
            }
            selectcounty(v3,v4);
        }
    }
}

/*
//依据省设置城市，县
*/
function selectCity() {
    var dropElement1 = document.getElementById(cascde.id1);
    var name = dropElement1.options[dropElement1.selectedIndex].value;     
    cascde.countyNodes = cascde.topNodeList[dropElement1.selectedIndex];      
    var province = document.getElementById(cascde.id2);
    var city = document.getElementById(cascde.id3);
    RemoveDropDownList(province);
    RemoveDropDownList(city);
    var citynodes;
    var countycodes;

    if (window.ActiveXObject) {
        citynodes = cascde.xmlDoc.selectSingleNode('//address/province [@name="' + name + '"]').childNodes;
    } else {
        citynodes = cascde.countyNodes.getElementsByTagName("city")
    }
    if (window.ActiveXObject) {
        countycodes = citynodes[0].childNodes;
    } else {
        countycodes = citynodes[0].getElementsByTagName("county")
    }
  
    if (citynodes.length > 0) {
        //城市
        for (var i = 0; i < citynodes.length; i++) {
            var provinceNode = citynodes[i];
            var option = document.createElement("option");
            option.value = provinceNode.getAttribute("name");
            option.text = provinceNode.getAttribute("name");
            province.add(option);
        }
        if (countycodes.length > 0) {
            //填充选择省份的第一个城市的县列表
            for (var i = 0; i < countycodes.length; i++) {
                var dropElement2 = document.getElementById(cascde.id2);
                var dropElement3 = document.getElementById(cascde.id3);
                //取当天省份下第一个城市列表
                
                //alert(cityNode.childNodes.length); 
                var option = document.createElement("option");
                option.value = countycodes[i].getAttribute("name");
                option.text = countycodes[i].getAttribute("name");
                dropElement3.add(option);
            }
        }
    selectcounty(0,0);
    }
}
/*
//设置县,区
*/
function selectcounty(v3,v4) {
    var dropElement1 = document.getElementById(cascde.id1);
    var dropElement2 = document.getElementById(cascde.id2);
    var name = dropElement2.options[dropElement2.selectedIndex].value;
    var dropElement3 = document.getElementById(cascde.id3);
    var countys = cascde.topNodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getElementsByTagName("county");

    if (cascde.openStreet == 1) {
        var city_code = cascde.topNodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getAttribute("code");
        if (city_code) {
            var left = city_code.substring(0,2);
            var xmlUrl = cascde.xmlfileBase + 'list/'+left+'/'+city_code+'.xml';
            xmlCityDoc = loadXmlFile(xmlUrl);

            if (window.ActiveXObject) {
                cascde.cityList = xmlCityDoc.selectSingleNode("address").childNodes.childNodes;
            } else {
                cascde.cityList = xmlCityDoc.childNodes[0].getElementsByTagName("county");
            }
        }
    }

    RemoveDropDownList(dropElement3);
    if (countys.length > 0) {
        for (var i = 0; i < countys.length; i++) {
            var countyNode = countys[i];
            var option = document.createElement("option");
            option.value = countyNode.getAttribute("name");
            option.text = countyNode.getAttribute("name");
            if(v3==option.value){
                option.selected=true;
            }
            dropElement3.add(option);
        }
        if (cascde.openStreet == 1) {
            selectstreet(v4);
        }
    }

}

function selectstreet(v4) {
    var dropElement1 = document.getElementById(cascde.id1);
    var dropElement2 = document.getElementById(cascde.id2);
    var name = dropElement2.options[dropElement2.selectedIndex].value;
    var dropElement3 = document.getElementById(cascde.id3);
    var dropElement4 = document.getElementById(cascde.id4);

    var area = dropElement3.options[dropElement3.selectedIndex].value;
    var area_code = cascde.topNodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getElementsByTagName("county")[dropElement3.selectedIndex].getAttribute("code");

    RemoveDropDownList(dropElement4);

    if(cascde.cityList && cascde.cityList.length>0) {
        for (var i = 0; i < cascde.cityList.length; i++) {
            var county = cascde.cityList[i];
            var county_code = county.getAttribute("code");

            if(county_code == area_code){
                var streetlist = county.getElementsByTagName("street");
                for (var m = 0; m < streetlist.length; m++) {
                    var street = streetlist[m];
                    var option = document.createElement("option");
                    option.value = street.getAttribute("name");
                    option.text = street.getAttribute("name");
                    if (v4 == option.value) {
                        option.selected = true;
                        cascde.nodeIndex = m;
                    }
                    dropElement4.add(option);
                }
            }
        }
    }
}

function RemoveDropDownList(obj) {
    if (obj) {
        var len = obj.options.length;
        if (len > 0) {  
            for (var i = len; i >= 0; i--) {
                obj.remove(i);
            }
        }
    }
}
/*
//读取xml文件
*/
function loadXmlFile(xmlFile) {
    var xmlDom = null;
    if (window.ActiveXObject) {
        xmlDom = new ActiveXObject("Microsoft.XMLDOM");
        xmlDom.async = false;
        xmlDom.load(xmlFile) || xmlDom.loadXML(xmlFile);//如果用的是XML字符串//如果用的是xml文件  
    } else if (document.implementation && document.implementation.createDocument) {
        var xmlhttp = new window.XMLHttpRequest();
        xmlhttp.open("GET", xmlFile, false);
        xmlhttp.send(null);
        xmlDom = xmlhttp.responseXML;
    } else {
        xmlDom = null;
    }
    return xmlDom;
}