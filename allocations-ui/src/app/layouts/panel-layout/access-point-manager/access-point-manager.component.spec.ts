import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { AccessPointManagerComponent } from './access-point-manager.component';

describe('AccessPointManagerComponent', () => {
  let component: AccessPointManagerComponent;
  let fixture: ComponentFixture<AccessPointManagerComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ AccessPointManagerComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(AccessPointManagerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
