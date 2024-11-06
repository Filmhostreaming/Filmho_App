import 'package:dtlive/provider/generalprovider.dart';
import 'package:dtlive/provider/homeprovider.dart';
import 'package:dtlive/provider/sectiondataprovider.dart';
import 'package:dtlive/utils/constant.dart';
import 'package:dtlive/utils/color.dart';
import 'package:dtlive/utils/sharedpre.dart';
import 'package:dtlive/widget/mypagebuilder.dart';
import 'package:dtlive/utils/utils.dart';
import 'package:dtlive/widget/sidemenu.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:onesignal_flutter/onesignal_flutter.dart';
import 'package:provider/provider.dart';
import 'package:sidebarx/sidebarx.dart';

class Home extends StatefulWidget {
  final String? pageName;
  const Home({Key? key, required this.pageName}) : super(key: key);

  @override
  State<Home> createState() => HomeState();
}

class HomeState extends State<Home> {
  late HomeProvider homeProvider;
  late SectionDataProvider sectionDataProvider;
  final _controller = SidebarXController(selectedIndex: 0, extended: true);
  final FirebaseAuth auth = FirebaseAuth.instance;
  SharedPre sharedPref = SharedPre();
  String? currentPage;

  @override
  void initState() {
    homeProvider = Provider.of<HomeProvider>(context, listen: false);
    sectionDataProvider =
        Provider.of<SectionDataProvider>(context, listen: false);
    currentPage = widget.pageName ?? "";
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _getData();
    });
    if (!kIsWeb) {
      OneSignal.Notifications.addClickListener(_handleNotificationOpened);
    }
  }

  // What to do when the user opens/taps on a notification
  _handleNotificationOpened(OSNotificationClickEvent result) async {
    /* id, video_type, type_id, user_id */
    debugPrint(
        "setNotificationOpenedHandler additionalData ===> ${result.notification.additionalData.toString()}");
    debugPrint(
        "setNotificationOpenedHandler user_id ===> ${result.notification.additionalData?['user_id']}");
    if (result.notification.additionalData != null &&
        result.notification.additionalData?['user_id'] != null) {
      Utils.setUserId(
          result.notification.additionalData?['user_id'].toString());
      Constant.userID =
          result.notification.additionalData?['user_id'].toString();
      await homeProvider.updateSideMenu();
      if (!mounted) return;
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(
            builder: (BuildContext context) => const Home(pageName: '')),
        (Route<dynamic> route) => false,
      );
    }

    debugPrint(
        "setNotificationOpenedHandler video_id ===> ${result.notification.additionalData?['id']}");
    debugPrint(
        "setNotificationOpenedHandler upcoming_type ===> ${result.notification.additionalData?['upcoming_type']}");
    debugPrint(
        "setNotificationOpenedHandler video_type ===> ${result.notification.additionalData?['video_type']}");
    debugPrint(
        "setNotificationOpenedHandler type_id ===> ${result.notification.additionalData?['type_id']}");

    if (result.notification.additionalData != null &&
        result.notification.additionalData?['id'] != null &&
        result.notification.additionalData?['upcoming_type'] != null &&
        result.notification.additionalData?['video_type'] != null &&
        result.notification.additionalData?['type_id'] != null) {
      String? videoID =
          result.notification.additionalData?['id'].toString() ?? "";
      String? upcomingType =
          result.notification.additionalData?['upcoming_type'].toString() ?? "";
      String? videoType =
          result.notification.additionalData?['video_type'].toString() ?? "";
      String? typeID =
          result.notification.additionalData?['type_id'].toString() ?? "";
      debugPrint("videoID =======> $videoID");
      debugPrint("upcomingType ==> $upcomingType");
      debugPrint("videoType =====> $videoType");
      debugPrint("typeID ========> $typeID");

      _controller.selectIndex(0);
      if (!mounted) return;
      Utils.openDetails(
        context: context,
        controller: _controller,
        videoId: int.parse(videoID),
        upcomingType: int.parse(upcomingType),
        videoType: int.parse(videoType),
        typeId: int.parse(typeID),
      );
    }
  }

_getData() async {
  // Obtener el símbolo de la moneda
  Utils.getCurrencySymbol();
  
  // Obtener el provider para la configuración general
  final generalProvider = Provider.of<GeneralProvider>(context, listen: false);
  
  // Leer el ID del usuario desde el almacenamiento local
  Constant.userID = await sharedPref.read("userid");
  debugPrint("userID :====HOME====> ${Constant.userID}");
  
  // Iniciar el proceso de carga en el HomeProvider
  await homeProvider.setLoading(true);

  // Obtener los tipos de secciones desde el HomeProvider
  await homeProvider.getSectionType();

  // Solo proceder si la carga ha terminado y los datos son correctos
  if (!homeProvider.loading) {
    // Verificar que la respuesta del servidor es válida
    if (homeProvider.sectionTypeModel.status == 200 &&
        homeProvider.sectionTypeModel.result != null &&
        (homeProvider.sectionTypeModel.result?.isNotEmpty ?? false)) {
      // Si hay resultados, obtener datos de la primera pestaña (tab)
      await getTabData(0);
    }
  }

  // Asegurarse de que la UI se actualice de forma segura después de la carga
  if (mounted) {
    setState(() {}); // Actualizar el estado de la pantalla
  }

  // Obtener la configuración general
  await generalProvider.getGeneralsetting();
}


  Future<void> setSelectedTab(int tabPos) async {
    if (!mounted) return;
    await homeProvider.setSelectedTab(tabPos);
    debugPrint("setSelectedTab position ====> $tabPos");
    sectionDataProvider.setTabPosition(tabPos);
  }

  Future<void> getTabData(int position) async {
    await setSelectedTab(position);
    await sectionDataProvider.setLoading(true);
    await sectionDataProvider.getSectionBanner(
        position == 0
            ? "0"
            : (homeProvider.sectionTypeModel.result?[position - 1].id),
        position == 0 ? "1" : "2");
    await sectionDataProvider.getSectionList(
        position == 0
            ? "0"
            : (homeProvider.sectionTypeModel.result?[position - 1].id),
        position == 0 ? "1" : "2");
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
        child: Row(
          children: [
            SideMenu(controller: _controller),
            Expanded(
              child: Center(
                child: MyPageBuilder(
                  controller: _controller,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
