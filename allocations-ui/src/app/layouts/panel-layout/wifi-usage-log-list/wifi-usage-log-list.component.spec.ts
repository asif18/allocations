import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { WifiUsageLogListComponent } from './wifi-usage-log-list.component';

describe('WifiUsageLogListComponent', () => {
  let component: WifiUsageLogListComponent;
  let fixture: ComponentFixture<WifiUsageLogListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ WifiUsageLogListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(WifiUsageLogListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
