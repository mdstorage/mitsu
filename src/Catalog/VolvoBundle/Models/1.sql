CREATE VIEW dbo.VehicleProfileDescriptions
AS
SELECT     vehicleprofile.Id,


  COALESCE((concat(CASE WHEN vehiclemodel.Description IS NULL
    THEN '' ELSE concat(', ', vehiclemodel.Description) END,
                  CASE WHEN modelyear.Description IS NULL
    THEN '' ELSE concat(', ', modelyear.Description) END,
                  CASE WHEN engine.Description IS NULL
    THEN '' ELSE concat(', ', engine.Description) END,
                  CASE WHEN transmission.Description IS NULL
    THEN '' ELSE concat(', ', transmission.Description) END,
                 CASE WHEN bodystyle.Description IS NULL
    THEN '' ELSE concat(', ', bodystyle.Description) END,
                  CASE WHEN steering.Description IS NULL
    THEN '' ELSE concat(', ', steering.Description) END,
                  CASE WHEN brakesystem.Description IS NULL
    THEN '' ELSE concat(', ', brakesystem.Description) END,
                  CASE WHEN structureweek.Description IS NULL
    THEN '' ELSE concat(', ', structureweek.Description) END,
                  CASE WHEN specialvehicle.Description IS NULL
    THEN '' ELSE concat(', ', specialvehicle.Description) END)
  ) , 'All Models*') AS FullTitle,




                      vehiclemodel.Description AS VehicleModelDesc, modelyear.Description AS ModelYearDesc, engine.Description AS EngineDesc,
                      transmission.Description AS TransmissionDesc, bodystyle.Description AS BodyStyleDesc, steering.Description AS SteeringDesc,
                      partnergroup.Description AS PartnerGroupDesc, brakesystem.Description AS BrakesSystemDesc, structureweek.Description AS StructureWeekDesc,
                     specialvehicle.Description AS SpecialVehicleDesc, ChassisNoFrom AS ChassiNoFrom, ChassisNoTo AS ChassiNoTo
FROM         vehicleprofile LEFT OUTER JOIN
  brakesystem ON vehicleprofile.fkBrakeSystem = brakesystem.Id LEFT OUTER JOIN
  structureweek ON vehicleprofile.fkStructureWeek = structureweek.Id LEFT OUTER JOIN
  partnergroup ON vehicleprofile.fkPartnerGroup = partnergroup.Id LEFT OUTER JOIN
  transmission ON vehicleprofile.fkTransmission = transmission.Id LEFT OUTER JOIN
                      nodeecu ON vehicleprofile.fkNodeECU = nodeecu.Id LEFT OUTER JOIN
                      engine ON vehicleprofile.fkEngine = engine.Id LEFT OUTER JOIN
                      vehiclemodel ON vehicleprofile.fkVehicleModel = vehiclemodel.Id LEFT OUTER JOIN
  bodystyle ON vehicleprofile.fkBodyStyle = bodystyle.Id LEFT OUTER JOIN
  steering ON vehicleprofile.fkSteering = steering.Id LEFT OUTER JOIN
  modelyear ON vehicleprofile.fkModelYear = modelyear.Id LEFT OUTER JOIN
  specialvehicle ON vehicleprofile.fkSpecialVehicle = specialvehicle.Id

