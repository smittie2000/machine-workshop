import { SimulationVersion, BodyType, ShapeType } from '../Enums';
export type CatalogueData = {
id: string,
name: string,
simulationVersion: SimulationVersion,
materials: MaterialData[],
parts: PartData[],
};
export type InventoryItemData = {
partKey: string,
quantity: number,
};
export type MaterialData = {
key: string,
name: string,
friction: number,
restitution: number,
};
export type PartData = {
key: string,
name: string,
materialKey: string,
bodyType: BodyType,
shapeType: ShapeType,
radiusMm: number | null,
widthMm: number | null,
heightMm: number | null,
massG: number | null,
visualKey: string,
};
export type PartInstanceData = {
id: string,
partKey: string,
xMm: number,
yMm: number,
rotationMilliDegrees: number,
};
export type PuzzleDocumentData = {
schemaVersion: number,
catalogueVersion: string,
instances: PartInstanceData[],
lockedInstanceIds: string[],
inventory: InventoryItemData[],
goal: RegionGoalData | null,
};
export type RegionGoalData = {
objectId: string,
xMm: number,
yMm: number,
widthMm: number,
heightMm: number,
consecutiveTicks: number,
};
export type StarterDocumentData = {
title: string,
document: PuzzleDocumentData,
};
export type ValidateDocumentData = {
document: PuzzleDocumentData,
};
export type ValidatedDocumentData = {
document: PuzzleDocumentData,
};
