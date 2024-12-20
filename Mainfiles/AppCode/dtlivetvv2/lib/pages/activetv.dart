import 'package:dtlive/provider/generalprovider.dart';
import 'package:dtlive/utils/color.dart';
import 'package:dtlive/utils/constant.dart';
import 'package:dtlive/utils/utils.dart';
import 'package:dtlive/widget/focusbase.dart';
import 'package:dtlive/widget/myimage.dart';
import 'package:dtlive/widget/mytext.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:sidebarx/sidebarx.dart';

class ActiveTV extends StatefulWidget {
  const ActiveTV({
    Key? key,
    required SidebarXController controller,
  })  : _controller = controller,
        super(key: key);

  // ignore: unused_field
  final SidebarXController _controller;

  @override
  State<ActiveTV> createState() => ActiveTVState();
}

class ActiveTVState extends State<ActiveTV> {
  late GeneralProvider generalProvider;
  String? strDeviceToken = "", strDeviceType = "1", tvLoginCode = "";

  @override
  void initState() {
    generalProvider = Provider.of<GeneralProvider>(context, listen: false);
    _getData();
    super.initState();
  }

  _getData() async {
    try {
      strDeviceToken = await FirebaseMessaging.instance.getToken();
    } catch (e) {
      debugPrint("FirebaseMessaging Exception ===> $e");
    }
    debugPrint("strDeviceToken ===> $strDeviceToken");
    await generalProvider.getTVLoginCode(strDeviceToken);

    if (!generalProvider.loading) {
      if (generalProvider.tvCodeModel.status == 200) {
        if (generalProvider.tvCodeModel.result != null &&
            (generalProvider.tvCodeModel.result?.length ?? 0) > 0) {
          tvLoginCode = generalProvider.tvCodeModel.result?[0].uniqueCode ?? "";
          debugPrint("tvLoginCode ===> $tvLoginCode");
        }
      }
    }
    Future.delayed(Duration.zero).then((value) {
      if (!mounted) return;
      setState(() {});
    });
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: appBgColor,
      body: SafeArea(
        child: Center(
          child: Container(
            constraints: BoxConstraints(
              maxWidth: MediaQuery.of(context).size.width > 400
                  ? 400
                  : MediaQuery.of(context).size.width,
            ),
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  if (!kIsWeb)
                    Align(
                      alignment: Alignment.topLeft,
                      child: FocusBase(
                        focusColor: white.withOpacity(0.5),
                        onPressed: () {
                          Navigator.pop(context);
                        },
                        onFocus: (isFocused) {},
                        child: Container(
                          width: 35,
                          height: 35,
                          alignment: Alignment.center,
                          padding: const EdgeInsets.all(8),
                          child: MyImage(
                            fit: BoxFit.contain,
                            imagePath: "back.png",
                          ),
                        ),
                      ),
                    ),
                  const SizedBox(height: 20),
                  MyText(
                    color: white,
                    text: "your_tvcode",
                    fontsizeNormal: 25,
                    multilanguage: true,
                    fontsizeWeb: 30,
                    textSpace: 1.0,
                    wordSpace: 2.0,
                    fontweight: FontWeight.bold,
                    maxline: 2,
                    overflow: TextOverflow.ellipsis,
                    textalign: TextAlign.center,
                    fontstyle: FontStyle.normal,
                  ),
                  const SizedBox(height: 20),
                  MyText(
                    color: otherColor,
                    text:
                        "Enter below code in your ${Constant.appName} mobile app to activate ${Constant.appName} tv app login.",
                    multilanguage: false,
                    fontsizeNormal: 18,
                    fontsizeWeb: 18,
                    textSpace: 1.0,
                    wordSpace: 2.0,
                    fontweight: FontWeight.w600,
                    maxline: 3,
                    overflow: TextOverflow.ellipsis,
                    textalign: TextAlign.center,
                    fontstyle: FontStyle.normal,
                  ),
                  const SizedBox(height: 30),

                  /* TV Code */
                  Container(
                    padding: const EdgeInsets.all(8.0),
                    child: Consumer<GeneralProvider>(
                      builder: (context, generalProvider, child) {
                        if (generalProvider.loading) {
                          return Utils.pageLoader();
                        } else {
                          return MyText(
                            color: white,
                            text: tvLoginCode ?? "",
                            multilanguage: false,
                            fontsizeNormal: 60,
                            fontsizeWeb: 60,
                            textSpace: 25.0,
                            fontweight: FontWeight.w800,
                            maxline: 3,
                            overflow: TextOverflow.ellipsis,
                            textalign: TextAlign.center,
                            fontstyle: FontStyle.normal,
                          );
                        }
                      },
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
