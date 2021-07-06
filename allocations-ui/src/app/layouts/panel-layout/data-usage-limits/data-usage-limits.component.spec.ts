import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DataUsageLimitsComponent } from './data-usage-limits.component';

describe('DataUsageLimitsComponent', () => {
  let component: DataUsageLimitsComponent;
  let fixture: ComponentFixture<DataUsageLimitsComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DataUsageLimitsComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DataUsageLimitsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
